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
            File::put($configPath, json_encode($storedTree, JSON_PRETTY_PRINT));
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
                'copy' => false,
                'type' => 'file',
            ];
        }
        foreach ($directories as $directory) {
            $fileTree[] = [
                'path' => $directory,
                'name' => basename($directory),
                'copy' => false,
                'type' => 'directory',
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
}
