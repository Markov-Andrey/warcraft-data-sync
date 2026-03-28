<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{
    public function loadConfigInfo(): array
    {
        $configPath = PathService::getconfigInfoPath();
        return File::exists($configPath) ? json_decode(File::get($configPath), true) : [];
    }

    public function saveConfigInfo(array $config): void
    {
        File::put(PathService::getconfigInfoPath(), json_encode($config, JSON_PRETTY_PRINT));
    }
}
