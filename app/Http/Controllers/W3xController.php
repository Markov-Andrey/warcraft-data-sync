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

        $directories = File::directories($currentPath);
        $files = File::files($currentPath);

        $jsonPaths = [];
        foreach ($json as $item) {
            $jsonPaths[$item['path']] = $item['copy'];
        }
        $directories = array_map(function ($directory) use ($jsonPaths) {
            $path = $directory;
            return [
                'path' => $path,
                'name' => basename($directory),
                'type' => 'directory',
                'copy' => $jsonPaths[$path] ?? false,
            ];
        }, $directories);
        $files = array_map(function ($file) use ($jsonPaths) {
            $path = $file->getRealPath();
            return [
                'path' => $path,
                'name' => $file->getBasename(),
                'type' => 'file',
                'copy' => $jsonPaths[$path] ?? false,
            ];
        }, $files);

        return view('project', [
            'directories' => $directories,
            'files' => $files,
            'currentPath' => $currentPath,
        ]);
    }
}
