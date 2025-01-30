<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{
    protected string $configFileName = '.files_config.json';
    protected string $configPath;
    protected string $parentProjectPath;
    public function __construct()
    {
        $this->parentProjectPath = config('w3x.parent_project');
        $this->configPath = $this->parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
    }

    /**
     * Собрать конфиг и перепроверить конфиг
     * @return void
     */
    public function initializeConfig(): void
    {
        if (!File::exists($this->configPath)) {
            $fileTree = $this->buildFileTree($this->parentProjectPath);
            File::put($this->configPath, json_encode($fileTree, JSON_PRETTY_PRINT));
        } else {
            $storedTree = json_decode(File::get($this->configPath), true);
            $fileTree = $this->buildFileTree($this->parentProjectPath);
            $this->syncData($storedTree, $fileTree);
            File::put($this->configPath, json_encode($storedTree, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Синхронизация между реальной архитектуры проекта и конфигом
     * @return void
     */
    public function syncData(&$storedTree, $fileTree): void
    {
        $actualPaths = array_column($fileTree, 'path');
        $storedTree = array_filter($storedTree, function ($item) use ($actualPaths) {
            return in_array($item['path'], $actualPaths);
        });
        $storedTree = array_values($storedTree);
        $existingPaths = array_column($storedTree, 'path');

        foreach ($fileTree as $newItem) {
            if (!in_array($newItem['path'], $existingPaths)) {
                $storedTree[] = $newItem;
            }
        }
    }

    /**
     * Рекурсия сбора архитектуры проекта
     * @return array
     */
    public function buildFileTree($directory): array
    {
        $fileTree = [];
        $directories = File::directories($directory);
        $files = File::files($directory);
        foreach ($files as $file) {
            $relativePath = str_replace($this->parentProjectPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $fileTree[] = [
                'path' => $relativePath,
                'name' => $file->getBasename(),
                'type' => 'file',
                'copy' => false,
                'copy_child' => '',
                'validated' => false,
            ];
        }
        foreach ($directories as $dir) {
            $relativePath = str_replace($this->parentProjectPath . DIRECTORY_SEPARATOR, '', $dir);
            $fileTree[] = [
                'path' => $relativePath,
                'name' => basename($dir),
                'type' => 'directory',
                'copy' => false,
                'copy_child' => '',
                'validated' => false,
            ];
            $fileTree = array_merge($fileTree, $this->buildFileTree($dir));
        }

        return $fileTree;
    }

    /**
     * Читатель конфиг файла
     * @return array
     */
    public function loadConfig()
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];
        $configPath = $parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
        if (File::exists($configPath)) {
            $jsonContent = File::get($configPath);
            return json_decode($jsonContent, true);
        }

        return [];
    }

    /**
     * Обработчик обновления статуса - копировать
     * @return bool
     */
    public function updateCopyStatus(string $path, bool $copy): bool
    {
        $config = json_decode(File::get($this->configPath), true);
        $updated = false;
        foreach ($config as &$item) {
            if ($item['path'] === $path) {
                $item['copy'] = $copy;
                $updated = true;
                break;
            }
        }
        if ($updated) {
            File::put($this->configPath, json_encode($config, JSON_PRETTY_PRINT));
        }

        return $updated;
    }

    /**
     * Обработчик обновления статуса - копировать в выбранные дочерние проекты
     * @return bool
     */
    public function updateCopyChild(string $path, string $child): bool
    {
        $config = json_decode(File::get($this->configPath), true);
        $updated = false;
        foreach ($config as &$item) {
            if ($item['path'] === $path) {
                $item['copy_child'] = $child;
                $updated = true;
                break;
            }
        }
        if ($updated) {
            File::put($this->configPath, json_encode($config, JSON_PRETTY_PRINT));
        }

        return $updated;
    }

    /**
     * Обновить в конфиге валидацию новых файлов
     * @return bool
     */
    public function updateValidateStatus(): bool
    {
        $config = json_decode(File::get($this->configPath), true);
        $updated = false;
        foreach ($config as &$item) {
            if (isset($item['validated']) && $item['validated'] === false) {
                $item['validated'] = true;
                $updated = true;
            }
        }
        if ($updated) {
            File::put($this->configPath, json_encode($config, JSON_PRETTY_PRINT));
        }
        return true;
    }

    /**
     * Копировать файлы дочерним проектам согласно конфигу (copy,copy_child)
     * @return bool
     */
    public function copyChildFiles(): bool
    {
        $child_projects = config('w3x.child_projects');
        $config = json_decode(File::get($this->configPath), true);
        $replacePatterns = include(config_path('w3x_replace.php'));

        foreach ($child_projects as $project) {
            foreach ($config as $item) {
                if ($item['copy'] && (!$item['copy_child'] || in_array($project['name'], explode(',', $item['copy_child'])))) {
                    $targetPath = $project['path'] . DIRECTORY_SEPARATOR . $item['path'];

                    if ($item['type'] === 'directory') {
                        if (!File::exists($targetPath)) {
                            File::makeDirectory($targetPath, 0777, true);
                        }
                        $this->copyFilesRecursive($this->parentProjectPath . DIRECTORY_SEPARATOR . $item['path'], $targetPath);
                    } elseif ($item['type'] === 'file') {
                        $sourceFile = $this->parentProjectPath . DIRECTORY_SEPARATOR . $item['path'];
                        if (File::exists($sourceFile)) {
                            $targetDir = dirname($targetPath);
                            if (!File::exists($targetDir)) {
                                File::makeDirectory($targetDir, 0777, true);
                            }
                            File::copy($sourceFile, $targetPath);

                            // Передаем **ВЕСЬ** $replacePatterns, а не `war3map.wts`
                            $this->processFileWithPatterns($replacePatterns, $targetPath, $project['name']);
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
    private function processFileWithPatterns(array $patterns, string $filePath, string $projectName): void
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
    private function copyFilesRecursive($sourceDir, $targetDir): void
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
