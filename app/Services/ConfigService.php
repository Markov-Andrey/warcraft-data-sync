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
        $config = config('w3x');
        $this->parentProjectPath = $config['parent_project'];
        $this->configPath = $this->parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
    }

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

    public function buildFileTree($directory): array
    {
        $fileTree = [];
        $rootPath = config('w3x.parent_project');
        $directories = File::directories($directory);
        $files = File::files($directory);
        foreach ($files as $file) {
            $relativePath = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
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
            $relativePath = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $dir);
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
     * Обновить параметр 'copy' для файла или директории в конфиге
     *
     * @param string $path Путь до файла или директории
     * @param bool $copy Новый статус 'copy'
     * @return bool Успешно ли обновлен файл
     */
    public function updateCopyStatus(string $path, bool $copy): bool
    {
        if (!File::exists($this->configPath)) {
            return false;
        }
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
    public function updateCopyChild(string $path, string $child): bool
    {
        if (!File::exists($this->configPath)) {
            return false;
        }
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
    public function updateValidateStatus(): bool
    {
        if (!File::exists($this->configPath)) {
            return false;
        }
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
    public function copyChildFiles(): bool
    {
        if (!File::exists($this->configPath)) {
            return false;
        }

        $child_projects = config('w3x.child_projects');
        $config = json_decode(File::get($this->configPath), true);

        foreach ($child_projects as $projectKey => $project) {

            foreach ($config as $item) {
                if ($item['copy']) {
                    if (($item['copy_child']) !== '') {
                        $copyChildArray = explode(',', $item['copy_child']);
                        if (!in_array($project['name'], $copyChildArray)) {
                            continue;
                        }
                    }
                    $targetPath = $project['path'] . DIRECTORY_SEPARATOR . $item['path'];

                    if ($item['type'] === 'directory') {
                        if (!File::exists($targetPath)) {
                            File::makeDirectory($targetPath, 0777, true);
                        }
                        $this->copyFilesRecursive($this->parentProjectPath . DIRECTORY_SEPARATOR . $item['path'], $targetPath);
                    }
                    elseif ($item['type'] === 'file') {
                        $sourceFile = $this->parentProjectPath . DIRECTORY_SEPARATOR . $item['path'];
                        if (File::exists($sourceFile)) {
                            $targetDir = dirname($targetPath);
                            if (!File::exists($targetDir)) {
                                File::makeDirectory($targetDir, 0777, true);
                            }
                            File::copy($sourceFile, $targetPath);
                        }
                    }
                }
            }
        }

        return true;
    }
    private function copyFilesRecursive($sourceDir, $targetDir)
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
