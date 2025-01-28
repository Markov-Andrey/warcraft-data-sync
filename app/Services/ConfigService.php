<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{
    protected string $configFileName = '.config.json';

    public function initializeConfig(): void
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];

        $configPath = $parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;

        if (!File::exists($configPath)) {
            $fileTree = $this->buildFileTree($parentProjectPath);
            File::put($configPath, json_encode($fileTree, JSON_PRETTY_PRINT));
        } else {
            $storedTree = json_decode(File::get($configPath), true);
            $currentTree = $this->buildFileTree($parentProjectPath);
            $this->syncDirectories($storedTree, $currentTree);
            File::put($configPath, json_encode($storedTree, JSON_PRETTY_PRINT));
        }
    }

    public function buildFileTree($directory): array
    {
        $fileTree = [
            'name' => basename($directory),
            'copy' => false,
            'type' => 'directory',
            'children' => [],
        ];

        $directories = File::directories($directory);
        $files = File::files($directory);

        foreach ($files as $file) {
            $fileTree['children'][] = [
                'name' => basename($file),
                'copy' => false,
                'type' => 'file',
            ];
        }
        foreach ($directories as $dir) {
            $fileTree['children'][] = $this->buildFileTree($dir);
        }

        return $fileTree;
    }

    public function syncDirectories(&$storedTree, $currentTree): void
    {
        $this->addNewFilesAndDirectories($storedTree, $currentTree); // Сначала добавляем новые файлы и директории
        $this->removeDeletedFilesAndDirectories($storedTree, $currentTree); // Затем удаляем файлы и директории, которых больше нет
        $this->updateCopyFlag($storedTree, $currentTree); // Обновляем флаг "copy" (если требуется)
    }
    public function addNewFilesAndDirectories(&$storedTree, $currentTree): void
    {
        foreach ($currentTree['children'] as $currentChild) {
            $storedChild = $this->findChildByName($storedTree['children'], $currentChild['name']);

            if (!$storedChild) {
                $storedTree['children'][] = $currentChild;
            } elseif ($storedChild['type'] == 'directory') {
                $this->syncDirectories($storedChild, $currentChild);
            }
        }
    }
    public function removeDeletedFilesAndDirectories(&$storedTree, $currentTree): void
    {
        foreach ($storedTree['children'] as $index => $storedChild) {
            $currentChild = $this->findChildByName($currentTree['children'], $storedChild['name']);

            if (!$currentChild) {
                unset($storedTree['children'][$index]);
            } elseif ($storedChild['type'] == 'directory') {
                $this->removeDeletedFilesAndDirectories($storedChild, $currentChild);
            }
        }
    }
    public function updateCopyFlag(&$storedTree, $currentTree): void
    {
        foreach ($storedTree['children'] as &$storedChild) {
            $currentChild = $this->findChildByName($currentTree['children'], $storedChild['name']);
            if ($currentChild && isset($currentChild['copy']) && $currentChild['copy'] !== false) {
                $storedChild['copy'] = $currentChild['copy'];
            }
            if ($storedChild['type'] == 'directory') {
                $this->updateCopyFlag($storedChild, $currentChild);
            }
        }
    }
    public function findChildByName($children, $name)
    {
        foreach ($children as $child) {
            if ($child['name'] == $name) {
                return $child;
            }
        }
        return null;
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
}
