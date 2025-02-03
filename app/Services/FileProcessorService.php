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
        $replacePatterns = include(config_path('w3x_replace.php'));

        foreach ($child_projects as $project) {
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
                        if (File::exists($sourceFile)) {
                            $targetDir = dirname($targetPath);
                            if (!File::exists($targetDir)) {
                                File::makeDirectory($targetDir, 0777, true);
                            }
                            File::copy($sourceFile, $targetPath);
                            FileProcessorService::processFileWithPatterns($replacePatterns, $targetPath, $project['name']);
                        }
                    }
                }
            }
        }
        InfoConfigService::lastSync();

        return true;
    }

    /**
     * Обрабатываем файл в проекте с применением паттернов замены
     */
    public static function processFileWithPatterns(array $patterns, string $filePath, string $projectName): void
    {
        $content = File::get($filePath);

        foreach ($patterns as $file => $filePatterns) {
            if ($file === basename($filePath)) {
                foreach ($filePatterns as $pattern => $replacement) {
                    if (str_contains($replacement, ':project_name')) {
                        $replacement = str_replace(':project_name', $projectName, $replacement);
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
        $projects = json_decode(env('CHILD_PROJECTS'), true);
        $parentProjectPath = env('PARENT_PROJECT');

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
