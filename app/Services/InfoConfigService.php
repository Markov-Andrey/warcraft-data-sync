<?php

namespace App\Services;

class InfoConfigService
{
    public static function defaultValues(): array
    {
        return [
            'last_checked' => '00.00.0000 00:00',
            'last_synced' => '00.00.0000 00:00',
            'last_build' => '00.00.0000 00:00',
            'current_project' => '',
            'build_version' => '0.0.0',
        ];
    }

    private static function updateConfigValue(string $key, mixed $value): void
    {
        $configService = new ConfigService();
        $info = $configService->loadConfigInfo();
        $info[$key] = $value;
        $configService->saveConfigInfo($info);
    }

    public static function lastSync(): void
    {
        self::updateConfigValue('last_synced', date('d.m.Y H:i'));
    }

    public static function lastCheck(): void
    {
        self::updateConfigValue('last_checked', date('d.m.Y H:i'));
    }

    public static function lastBuild(): void
    {
        self::updateConfigValue('last_build', date('d.m.Y H:i'));
    }

    public static function selectedProject($project): void
    {
        self::updateConfigValue('current_project', $project);
    }

    public static function selectedVersion($str): void
    {
        self::updateConfigValue('build_version', $str);
    }
}
