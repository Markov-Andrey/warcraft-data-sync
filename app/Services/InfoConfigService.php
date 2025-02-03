<?php

namespace App\Services;

class InfoConfigService
{
    public static function updateTimestamp(string $key): void
    {
        $configService = new ConfigService();
        $info = $configService->loadConfigInfo();
        $info[$key] = date('d.m.Y H:i');
        $configService->saveConfigInfo($info);
    }

    public static function lastSync(): void
    {
        self::updateTimestamp('last_synced');
    }

    public static function lastCheck(): void
    {
        self::updateTimestamp('last_checked');
    }

    public static function lastBuild(): void
    {
        self::updateTimestamp('last_build');
    }

    public static function selectedProject($project): void
    {
        $configService = new ConfigService();
        $info = $configService->loadConfigInfo();
        $info['current_project'] = $project;
        $configService->saveConfigInfo($info);
    }
}
