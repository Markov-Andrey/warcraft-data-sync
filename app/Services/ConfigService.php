<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{

    /**
     * Загрузка конфига
     */
    public function loadConfig(): array
    {
        $configPath = PathService::getConfigPath();
        return File::exists($configPath) ? json_decode(File::get($configPath), true) : [];
    }

    /**
     * Сохранение конфига
     */
    public function saveConfig(array $config): void
    {
        File::put(PathService::getConfigPath(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Инициализация конфигурации
     */
    public function initializeConfig(): void
    {
        $configPath = PathService::getConfigPath();
        $parentProjectPath = PathService::getParentProjectPath();

        if (!File::exists($configPath)) {
            $fileTree = $this->buildFileTree($parentProjectPath);
        } else {
            $storedTree = $this->loadConfig();
            $fileTree = $this->buildFileTree($parentProjectPath);
            $this->syncData($storedTree, $fileTree);
            $fileTree = $storedTree;
        }

        $this->saveConfig($fileTree);
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
        return [
            'path' => str_replace($dir . DIRECTORY_SEPARATOR, '', $path),
            'name' => basename($path),
            'type' => $type,
            'copy' => false,
            'copy_child' => '',
            'validated' => false,
        ];
    }

    /**
     * Универсальный метод обновления параметров элемента конфига
     */
    private function updateConfigItem(string $path, string $key, mixed $value): bool
    {
        $config = $this->loadConfig();
        $updated = false;

        foreach ($config as &$item) {
            if ($item['path'] === $path) {
                $item[$key] = $value;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveConfig($config);
        }

        return $updated;
    }

    /**
     * Обновление статуса копирования
     */
    public function updateCopyStatus(string $path, bool $copy): bool
    {
        return $this->updateConfigItem($path, 'copy', $copy);
    }

    /**
     * Обновление дочернего копирования
     */
    public function updateCopyChild(string $path, string $child): bool
    {
        return $this->updateConfigItem($path, 'copy_child', $child);
    }

    /**
     * Обновление статуса валидации новых файлов
     */
    public function updateValidateStatus(): bool
    {
        $config = $this->loadConfig();
        $updated = false;

        foreach ($config as &$item) {
            if (!$item['validated']) {
                $item['validated'] = true;
                $updated = true;
            }
        }

        if ($updated) {
            $this->saveConfig($config);
        }

        return $updated;
    }
}
