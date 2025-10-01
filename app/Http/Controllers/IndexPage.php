<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use App\Services\PathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class IndexPage extends Controller
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index()
    {
        $parentProjectPath = PathService::getParentProjectPath();
        $parentChildPath = PathService::getChildProject();

        $this->configService->initializeConfig();
        $configInfo = collect($this->configService->loadConfigInfo());
        $copyFiles = collect(config('w3x_const.copy'))
            ->map(fn($path) => $parentProjectPath . DIRECTORY_SEPARATOR . $path)
            ->toArray();
        $exceptionsFiles = collect(config('w3x_const.exceptions'))
            ->map(fn($path) => $parentProjectPath . DIRECTORY_SEPARATOR . $path)
            ->toArray();

        return view('project', [
            'configInfo' => $configInfo,
            'rootPath' => $parentProjectPath,
            'child_projects' => $parentChildPath ?? [],
            'copyFiles' => $copyFiles ?? [],
            'exceptionsFiles' => $exceptionsFiles ?? [],
        ]);
    }
}
