<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileProcessorService
{
    /**
     * Копировать файлы дочерним проектам согласно конфигу (copy,copy_child)
     * @return bool
     */
    public static function copyChildFiles(): bool
    {
        $configPath = PathService::getConfigFilePath();
        $child_projects = PathService::getChildProject();
        $parentProjectPath = PathService::getParentProjectPath();
        $config = json_decode(File::get($configPath), true);

        foreach ($child_projects as $key => $project) {
            foreach ($config as $item) {
                $allowedProjects = array_map('trim', explode(',', $item['copy_child']));
                $isAllowed = ($item['copy_child'] === '') || in_array($project['name'], $allowedProjects);

                if ($item['copy'] && $isAllowed) {
                    $targetPath = $project['path'] . DIRECTORY_SEPARATOR . $item['path'];

                    if ($item['type'] === 'directory') {
                        if (!File::exists($targetPath)) {
                            File::makeDirectory($targetPath, 0777, true);
                        }
                        FileProcessorService::copyFilesRecursive($parentProjectPath . DIRECTORY_SEPARATOR . $item['path'], $targetPath);
                    } elseif ($item['type'] === 'file') {
                        $sourceFile = $parentProjectPath . DIRECTORY_SEPARATOR . $item['path'];
                        $targetDir = dirname($targetPath);
                        if (!File::exists($targetDir)) {
                            File::makeDirectory($targetDir, 0777, true);
                        }
                        File::copy($sourceFile, $targetPath);

                        if (basename($targetPath) === 'war3map.j') {
                            // self::processWar3MapJ($sourceFile, $targetPath); // TODO тут логика словить содержимое и грамотно смержить и заменить переменные, чтобы не пересохранять код заново
                        }
                    }
                }
            }
        }
        InfoConfigService::lastSync();

        return true;
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
        $swapFiles = config('w3x_const');
        $projects = PathService::getChildProject();
        $parentProjectPath = PathService::getParentProjectPath();

        if (!isset($projects[$select])) {
            return response()->json(['success' => false, 'message' => 'Проект не найден']);
        }

        $childProjectDir = $projects[$select]['path'];

        foreach ($swapFiles as $file) {
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
