<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

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
                if ($item['copy'] && (!$item['copy_child'] || in_array($project['name'], explode(',', $item['copy_child'])))) {
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
}
