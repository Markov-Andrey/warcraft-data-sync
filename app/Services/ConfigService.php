<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{
    public function loadConfigFiles(): array
    {
        $configPath = PathService::getConfigFilePath();
        return File::exists($configPath) ? json_decode(File::get($configPath), true) : [];
    }
    public function loadConfigInfo(): array
    {
        $configPath = PathService::getconfigInfoPath();
        return File::exists($configPath) ? json_decode(File::get($configPath), true) : [];
    }
    public function saveConfigFiles(array $config): void
    {
        File::put(PathService::getConfigFilePath(), json_encode($config, JSON_PRETTY_PRINT));
    }
    public function saveConfigInfo(array $config): void
    {
        File::put(PathService::getconfigInfoPath(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Инициализация конфигурации
     */
    public function initializeConfig(): void
    {
        $configDir = PathService::getConfigDirPath();
        $configFile = PathService::getConfigFilePath();
        $configInfoFile = PathService::getconfigInfoPath();
        $parentProjectPath = PathService::getParentProjectPath();

        if (!File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }
        if (!File::exists($configInfoFile)) {
            $data = InfoConfigService::defaultValues();
            File::put($configInfoFile, json_encode($data, JSON_PRETTY_PRINT));
        }

        $fileTree = $this->buildFileTree($parentProjectPath);

        if (!File::exists($configFile)) {
            $this->saveConfigFiles($fileTree);
        } else {
            $storedTree = $this->loadConfigFiles();
            $this->syncData($storedTree, $fileTree);
            $this->saveConfigFiles($storedTree);
        }
    }

    /**
     * Синхронизация реальной структуры проекта с конфигом
     */
    private function syncData(array &$storedTree, array $fileTree): void
    {
        $actualPaths = array_column($fileTree, 'path');
        $storedTree = array_values(array_filter($storedTree, fn($item) => in_array($item['path'], $actualPaths)));

        foreach ($fileTree as $newItem) {
            if (!in_array($newItem['path'], array_column($storedTree, 'path'))) {
                $storedTree[] = $newItem;
            }
        }
    }

    /**
     * Построение дерева файлов и директорий
     */
    private function buildFileTree(string $directory): array
    {
        $fileTree = [];

        foreach (File::files($directory) as $file) {
            $fileTree[] = $this->formatFileItem($file->getPathname(), 'file');
        }

        foreach (File::directories($directory) as $dir) {
            $fileTree[] = $this->formatFileItem($dir, 'directory');
            $fileTree = array_merge($fileTree, $this->buildFileTree($dir));
        }

        return $fileTree;
    }

    /**
     * Форматирование элемента (файла или директории)
     */
    private function formatFileItem(string $path, string $type): array
    {
        $dir = PathService::getParentProjectPath();
        $w3x = config('w3x_const');

        $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
        $isCopyAllowed = !in_array($relativePath, $w3x);

        return [
            'path' => $relativePath,
            'name' => basename($path),
            'type' => $type,
            'copy' => $isCopyAllowed,
            'copy_child' => '',
            'validated' => false,
        ];
    }
}
