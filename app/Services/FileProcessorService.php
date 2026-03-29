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

    /**
     * Обмен данных из инфо карты
     *
     * @param string $childW3iPath
     * @param string $parentW3iPath
     * @param array $projectData
     * @return void
     */
    private static function patchMapInfo(string $childW3iPath, string $parentW3iPath, array $projectData): void
    {
        copy($childW3iPath, $parentW3iPath);

        $jsonPath = $parentW3iPath . '.json';
        MapConverterService::convertToJson($parentW3iPath, $parentW3iPath);

        $json = json_decode(file_get_contents($jsonPath), true);
        $info = $projectData['info'] ?? [];

        // Patch map fields
        $mapInfo = $info['map'] ?? [];
        foreach ($mapInfo as $key => $value) {
            $json['map'][$key] = $value;
        }

        // Patch players by index
        foreach (($info['players'] ?? []) as $i => $player) {
            if (isset($json['players'][$i])) {
                $json['players'][$i]['name'] = $player['name'];
            }
        }

        // Patch forces by index
        foreach (($info['forces'] ?? []) as $i => $force) {
            if (isset($json['forces'][$i])) {
                $json['forces'][$i]['name'] = $force['name'];
            }
        }

        file_put_contents($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $outputDir = dirname($parentW3iPath);
        MapConverterService::convertToWar($jsonPath, $parentW3iPath);
        $toolOutput = $outputDir . DIRECTORY_SEPARATOR . pathinfo($parentW3iPath, PATHINFO_FILENAME);
        if (file_exists($toolOutput)) {
            if (file_exists($parentW3iPath)) {
                unlink($parentW3iPath);
            }
            rename($toolOutput, $parentW3iPath);
        }
    }

    public static function switch($select)
    {
        $w3xConst = config('w3x_const');
        $swapFiles = $w3xConst['copy'] ?? [];
        $projects = PathService::getChildProject();
        $parentProjectPath = PathService::getParentProjectPath();
        $currentProject = InfoConfigService::load('current_project');

        if (!isset($projects[$select])) {
            return response()->json(['success' => false, 'message' => 'Проект не найден']);
        }

        // Step 1: parent → current child (save parent state into current child before switching)
        if ($currentProject && isset($projects[$currentProject])) {
            $currentChildDir = $projects[$currentProject]['path'];
            foreach ($swapFiles as $file) {
                if (!is_string($file)) {
                    continue;
                }
                $parentFilePath = $parentProjectPath . DIRECTORY_SEPARATOR . $file;
                $currentChildFilePath = $currentChildDir . DIRECTORY_SEPARATOR . $file;
                if (!file_exists($parentFilePath)) {
                    return response()->json(['success' => false, 'message' => "Файл не найден в родителе: $file"]);
                }
                if (!copy($parentFilePath, $currentChildFilePath)) {
                    return response()->json(['success' => false, 'message' => "Ошибка сохранения в текущий проект: $file"]);
                }
            }
        }

        // Step 2: selected child → parent (load selected child's files into parent)
        $selectedChildDir = $projects[$select]['path'];
        foreach ($swapFiles as $file) {
            if (!is_string($file)) {
                continue;
            }
            $selectedChildFilePath = $selectedChildDir . DIRECTORY_SEPARATOR . $file;
            $parentFilePath = $parentProjectPath . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($selectedChildFilePath)) {
                return response()->json(['success' => false, 'message' => "Файл не найден в выбранном проекте: $file"]);
            }

            if ($file === 'war3map.w3i') {
                self::patchMapInfo($selectedChildFilePath, $parentFilePath, $projects[$select]);
                continue;
            }

            if (!copy($selectedChildFilePath, $parentFilePath)) {
                return response()->json(['success' => false, 'message' => "Ошибка загрузки из выбранного проекта: $file"]);
            }
        }

        InfoConfigService::selectedProject($select);

        return response()->json(['success' => true, 'message' => 'Файлы успешно заменены']);
    }
}
