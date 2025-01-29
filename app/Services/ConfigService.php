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
        $directories = File::directories($directory);
        $files = File::files($directory);
        foreach ($files as $file) {
            $fileTree[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getBasename(),
                'type' => 'file',
                'copy' => false,
                'validated' => false,
            ];
        }
        foreach ($directories as $directory) {
            $fileTree[] = [
                'path' => $directory,
                'name' => basename($directory),
                'type' => 'directory',
                'copy' => false,
                'validated' => false,
            ];
            $fileTree = array_merge($fileTree, $this->buildFileTree($directory));
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
    public function updateCopyStatus(string $path, bool $copy)
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
}
