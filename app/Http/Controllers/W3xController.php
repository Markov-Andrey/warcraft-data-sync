<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class W3xController extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index(Request $request)
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];
        $currentPath = $request->get('path', $parentProjectPath);

        if (!str_starts_with($currentPath, $parentProjectPath)) {
            abort(403, 'Access denied to the requested directory.');
        }

        $this->configService->initializeConfig();
        $json = $this->configService->loadConfig();

        $currentStructure = $this->buildCurrentStructure($currentPath);

        $this->syncWithConfig($currentStructure, $json);


        $directories = array_map('basename', File::directories($currentPath));
        $files = array_map('basename', File::files($currentPath));

        return view('project', [
            'directories' => $directories,
            'files' => $files,
            'currentPath' => $currentPath,
        ]);
    }

    public function syncWithConfig(&$currentStructure, &$config)
    {
        foreach ($currentStructure['children'] as &$child) {
            $configChild = $this->findChildInConfig($config['children'], $child['name']);

            if ($configChild) {
                // Синхронизируем параметр "copy" из конфигурации
                $child['copy'] = $configChild['copy'];
            }

            // Если это директория, рекурсивно синхронизируем вложенные элементы
            if ($child['type'] == 'directory') {
                $this->syncWithConfig($child, $configChild);
            }
        }

        // Проверяем, если в конфиге есть файлы, которых нет в текущей структуре, добавляем их
        foreach ($config['children'] as $configChild) {
            if ($configChild['type'] == 'file' && !in_array($configChild['name'], array_column($currentStructure['children'], 'name'))) {
                $currentStructure['children'][] = [
                    'name' => $configChild['name'],
                    'copy' => $configChild['copy'],
                    'type' => 'file',
                ];
            }
        }
    }

    public function findChildInConfig($configChildren, $name)
    {
        foreach ($configChildren as $configChild) {
            if ($configChild['name'] == $name) {
                return $configChild;
            }
        }
        return null;
    }

    public function buildCurrentStructure($directory)
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
            $fileTree['children'][] = $this->buildCurrentStructure($dir);
        }

        return $fileTree;
    }

    public function compareAndUpdateFiles(&$currentStructure, $config)
    {
        foreach ($currentStructure['children'] as &$child) {
            $configChild = $this->findChildInConfig($config['children'], $child['name']);

            if ($configChild) {
                $child['copy'] = $configChild['copy'];
            }

            if ($child['type'] == 'directory') {
                $this->compareAndUpdateFiles($child, $configChild);
            }
        }

        foreach ($config['children'] as $configChild) {
            if ($configChild['type'] == 'file' && !in_array($configChild['name'], array_column($currentStructure['children'], 'name'))) {
                $currentStructure['children'][] = [
                    'name' => $configChild['name'],
                    'copy' => $configChild['copy'],
                    'type' => 'file',
                ];
            }
        }
    }
}
