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
        $config = $this->configService->loadConfig();

        $directories = [];
        $files = [];

        if (File::exists($currentPath) && File::isDirectory($currentPath)) {
            $directories = array_map('basename', File::directories($currentPath));
            $files = array_map('basename', File::files($currentPath));
        }

        return view('project', [
            'directories' => $directories,
            'files' => $files,
            'currentPath' => $currentPath,
        ]);
    }

    public function updateConfig(Request $request): \Illuminate\Http\RedirectResponse
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];

        $this->configService->updateParameters($parentProjectPath, $request->all());

        return redirect()->back()->with('success', 'Configuration updated successfully!');
    }
}
