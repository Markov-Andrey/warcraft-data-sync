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

    public function projectInfo()
    {
        $parentProjectPath = PathService::getParentProjectPath();

        $this->configService->initializeConfig();
        $configInfo = $this->configService->loadConfigInfo();
        $copyFiles = collect(config('w3x_const.copy'))
            ->map(fn($path) => basename($path))
            ->values()
            ->toArray();
        $exceptionsFiles = collect(config('w3x_const.exceptions'))
            ->map(fn($path) => basename($path))
            ->values()
            ->toArray();

        return response()->json([
            'configInfo'      => $configInfo,
            'childProjects'   => PathService::getChildProject() ?? [],
            'copyFiles'       => $copyFiles,
            'exceptionsFiles' => $exceptionsFiles,
        ]);
    }
}
