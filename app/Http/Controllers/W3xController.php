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
        $jsonCopy = collect($this->configService->loadConfig())->pluck('copy', 'path');
        $jsonValid = collect($this->configService->loadConfig())->pluck('validated', 'path');

        $items = collect(File::directories($currentPath))
            ->merge(File::files($currentPath))
            ->map(fn($item) => [
                'path' => $path = is_string($item) ? $item : $item->getRealPath(),
                'relativePath' => $relativePath = str_replace($parentProjectPath . DIRECTORY_SEPARATOR, '', $path),
                'name' => basename($item),
                'type' => is_string($item) ? 'directory' : 'file',
                'copy' => $jsonCopy[$relativePath] ?? false,
                'validated' => $jsonValid[$relativePath] ?? false,
            ])
            ->groupBy('type');

        return view('project', [
            'rootPath' => $parentProjectPath,
            'child_projects' => $config['child_projects'] ?? [],
            'directories' => $items['directory'] ?? [],
            'files' => $items['file'] ?? [],
            'currentPath' => $currentPath,
        ]);
    }
}
