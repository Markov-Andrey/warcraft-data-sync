<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FileProcessorService
{
    /**
     * Копировать файлы из parent во все child-проекты,
     * пропуская файлы из w3x_const['copy'] и w3x_const['exceptions']
     */
    public static function copyChildFiles(): bool
    {
        $childProjects = PathService::getChildProject();
        $parentProjectPath = PathService::getParentProjectPath();
        $w3xConst = config('w3x_const');

        $excluded = array_merge(
            $w3xConst['copy'] ?? [],
            $w3xConst['exceptions'] ?? []
        );

        // Все individual пути по всем детям — для использования в очистке
        $allIndividual = array_unique(array_map(
            fn($p) => str_replace('\\', '/', $p),
            array_merge(...array_values(array_map(fn($p) => $p['individual'] ?? [], $childProjects)))
        ));

        foreach (File::allFiles($parentProjectPath) as $file) {
            $relativePath = str_replace($parentProjectPath . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $normalizedRelative = str_replace('\\', '/', $relativePath);

            $isExcluded = false;
            foreach ($excluded as $item) {
                if ($file->getFilename() === $item
                    || $normalizedRelative === $item
                    || str_starts_with($normalizedRelative, $item . '/')) {
                    $isExcluded = true;
                    break;
                }
            }

            if ($isExcluded) {
                continue;
            }

            foreach ($childProjects as $project) {
                $targetPath = $project['path'] . DIRECTORY_SEPARATOR . $relativePath;
                $targetDir = dirname($targetPath);

                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0777, true);
                }

                File::copy($file->getRealPath(), $targetPath);
            }
        }

        // Чистим мусор в каждом child
        foreach ($childProjects as $project) {
            $myIndividual = array_map(
                fn($p) => str_replace('\\', '/', $p),
                $project['individual'] ?? []
            );
            self::cleanChildProject($project['path'], $parentProjectPath, $excluded, $myIndividual, $allIndividual);
        }

        InfoConfigService::lastSync();

        return true;
    }

    /**
     * Удалить из child файлы и пустые директории если:
     * - файла нет в parent, ИЛИ
     * - файл есть в allIndividual но не в myIndividual (чужой индивидуальный)
     * Не трогать: excluded и myIndividual
     */
    private static function cleanChildProject(
        string $childPath,
        string $parentPath,
        array $excluded,
        array $myIndividual,
        array $allIndividual
    ): void {
        foreach (File::allFiles($childPath) as $file) {
            $relativePath = str_replace($childPath . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $normalizedRelative = str_replace('\\', '/', $relativePath);

            // Защищаем: excluded и myIndividual
            $isProtected = false;
            foreach (array_merge($excluded, $myIndividual) as $item) {
                $normalizedItem = str_replace('\\', '/', $item);
                if ($normalizedRelative === $normalizedItem
                    || str_starts_with($normalizedRelative, rtrim($normalizedItem, '/') . '/')) {
                    $isProtected = true;
                    break;
                }
            }

            if ($isProtected) {
                continue;
            }

            // Удаляем если чужой individual или файла нет в parent
            $isForeignIndividual = false;
            foreach ($allIndividual as $item) {
                if ($normalizedRelative === $item
                    || str_starts_with($normalizedRelative, rtrim($item, '/') . '/')) {
                    $isForeignIndividual = true;
                    break;
                }
            }

            if ($isForeignIndividual || !File::exists($parentPath . DIRECTORY_SEPARATOR . $relativePath)) {
                File::delete($file->getRealPath());
            }
        }

        // Удаляем пустые директории (кроме защищённых)
        foreach (File::directories($childPath) as $dir) {
            $relativeDirName = str_replace('\\', '/', str_replace($childPath . DIRECTORY_SEPARATOR, '', $dir));

            $isProtected = false;
            foreach (array_merge($excluded, $myIndividual) as $item) {
                $normalizedItem = str_replace('\\', '/', $item);
                if ($relativeDirName === $normalizedItem || str_starts_with($relativeDirName, rtrim($normalizedItem, '/') . '/')) {
                    $isProtected = true;
                    break;
                }
            }

            if (!$isProtected) {
                self::deleteEmptyDirectories($dir);
            }
        }
    }

    /**
     * Рекурсивно удалить пустые директории снизу вверх
     */
    private static function deleteEmptyDirectories(string $dir): void
    {
        foreach (File::directories($dir) as $subDir) {
            self::deleteEmptyDirectories($subDir);
        }

        if (empty(File::files($dir)) && empty(File::directories($dir))) {
            File::deleteDirectory($dir);
        }
    }

    protected static function processWar3MapJ(string $sourceFile, string $targetFile): void
    {
        $parentContent = File::get($sourceFile);
        $childContent = File::get($targetFile);

        $keyBlocks = [
            'Main Initialization',
            'Players',
            'Map Configuration',
        ];

        // Разбиваем содержимое на строки
        $childLines = explode("\n", $childContent);
        $parentLines = explode("\n", $parentContent);

        $mergedLines = [];
        $collectingKey = null;

        // Пройдем по родителю и вставляем его ключевые блоки только если ребенок пустой
        foreach ($parentLines as $line) {
            foreach ($keyBlocks as $key) {
                if (strpos($line, $key) !== false) {
                    $collectingKey = $key;
                    break;
                }
            }

            if ($collectingKey !== null) {
                // Проверяем, есть ли блок в ребенке
                $childBlockStart = null;
                $childBlockLines = [];
                $insideChildBlock = false;

                foreach ($childLines as $childLine) {
                    if (strpos($childLine, $collectingKey) !== false) {
                        $insideChildBlock = true;
                    }

                    if ($insideChildBlock) {
                        $childBlockLines[] = $childLine;
                        // Конец блока: следующий ключ или конец файла
                        foreach ($keyBlocks as $key2) {
                            if ($key2 !== $collectingKey && strpos($childLine, $key2) !== false) {
                                $insideChildBlock = false;
                            }
                        }
                    }
                }

                // Если блок ребенка пустой — вставляем блок родителя
                if (empty(trim(implode("\n", $childBlockLines)))) {
                    $mergedLines[] = $line;
                } else {
                    $mergedLines = array_merge($mergedLines, $childBlockLines);
                }

                $collectingKey = null;
                continue;
            }

            // Остальные строки родителя просто добавляем
            $mergedLines[] = $line;
        }

        $mergedContent = implode("\n", $mergedLines);

        Log::info($mergedContent);
        // File::put($targetFile, $mergedContent);
    }

    /**
     * Обрабатываем файл в проекте с применением паттернов замены
     */
    public static function processFileWithPatterns(array $patterns, string $filePath, array $project): void
    {
        $content = File::get($filePath);

        $replacements = [
            ':project_name' => $project['name'],
            ':project_key' => $project['key'],
            ':description' => $project['description'],
            ':type_game' => $project['type_game'],
        ];

        foreach ($patterns as $file => $filePatterns) {
            if ($file === basename($filePath)) {
                foreach ($filePatterns as $pattern => $replacement) {
                    foreach ($replacements as $key => $value) {
                        if (str_contains($replacement, $key)) {
                            $replacement = str_replace($key, $value, $replacement);
                        }
                    }
                    $content = preg_replace($pattern, $replacement, $content);
                }
            }
        }

        File::put($filePath, $content);
    }
    /**
     * Рекурсия, если директория показана к полному копированию
     * @return void
     */
    public static function copyFilesRecursive($sourceDir, $targetDir): void
    {
        $sourceDir = realpath($sourceDir) ?: $sourceDir;
        $files = File::allFiles($sourceDir);

        foreach ($files as $file) {
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . $relativePath;

            $targetDirPath = dirname($targetFile);
            if (!File::exists($targetDirPath)) {
                File::makeDirectory($targetDirPath, 0777, true);
            }

            File::copy($file->getRealPath(), $targetFile);
        }
    }

    public static function switch($select)
    {
        $w3xConst = config('w3x_const');
        $swapFiles = $w3xConst['copy'] ?? [];
        $projects = PathService::getChildProject();
        $parentProjectPath = PathService::getParentProjectPath();

        if (!isset($projects[$select])) {
            return response()->json(['success' => false, 'message' => 'Проект не найден']);
        }

        $childProjectDir = $projects[$select]['path'];

        foreach ($swapFiles as $file) {
            if (!is_string($file)) {
                continue;
            }

            $childFilePath = $childProjectDir . DIRECTORY_SEPARATOR . $file;
            $parentFilePath = $parentProjectPath . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($childFilePath)) {
                continue;
            }
            if (!is_writable($parentProjectPath)) {
                return response()->json(['success' => false, 'message' => "Нет прав на запись"]);
            }
            if (!copy($childFilePath, $parentFilePath)) {
                return response()->json(['success' => false, 'message' => "Ошибка копирования $file"]);
            }
        }
        InfoConfigService::selectedProject($select);

        return response()->json(['success' => true, 'message' => 'Файлы успешно заменены']);
    }
}
