<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class IndexPage extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index(Request $request)
    {
        $parentProjectPath = env('PARENT_PROJECT');
        $parentChildPath = json_decode(env('CHILD_PROJECTS'), true);
        $currentPath = $request->get('path', $parentProjectPath);

        if (!str_starts_with($currentPath, $parentProjectPath)) {
            abort(403, 'Access denied to the requested directory.');
        }

        $this->configService->initializeConfig();
        $jsonCopy = collect($this->configService->loadConfigFiles())->pluck('copy', 'path');
        $jsonCopyChild = collect($this->configService->loadConfigFiles())->pluck('copy_child', 'path');
        $jsonValid = collect($this->configService->loadConfigFiles())->pluck('validated', 'path');
        $countNewFiles = $jsonValid->filter(fn($validated) => $validated === false)->count();
        $configInfo = collect($this->configService->loadConfigInfo());
        $constantFiles = collect(config('w3x_const'))
            ->map(fn($path) => $parentProjectPath . DIRECTORY_SEPARATOR . $path)
            ->toArray();

        $items = collect(File::directories($currentPath))
            ->merge(File::files($currentPath))
            ->map(fn($item) => [
                'path' => $path = is_string($item) ? $item : $item->getRealPath(),
                'relativePath' => $relativePath = str_replace($parentProjectPath . DIRECTORY_SEPARATOR, '', $path),
                'name' => basename($item),
                'type' => is_string($item) ? 'directory' : 'file',
                'copy' => $jsonCopy[$relativePath] ?? false,
                'copy_child' => $jsonCopyChild[$relativePath] ?? '',
                'validated' => $jsonValid[$relativePath] ?? false,
            ])
            ->groupBy('type');

        return view('project', [
            'configInfo' => $configInfo,
            'rootPath' => $parentProjectPath,
            'child_projects' => $parentChildPath ?? [],
            'directories' => $items['directory'] ?? [],
            'files' => $items['file'] ?? [],
            'currentPath' => $currentPath,
            'constantFiles' => $constantFiles,
            'countNewFiles' => $countNewFiles ?? null,
        ]);
    }
}
