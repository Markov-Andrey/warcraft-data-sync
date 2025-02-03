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

    public static function load($key): ?string
    {
        $data = (new ConfigService)->loadConfigInfo();
        return $data[$key] ?? null;
    }

    private static function save(string $key, mixed $value): void
    {
        $configService = new ConfigService();
        $info = $configService->loadConfigInfo();
        $info[$key] = $value;
        $configService->saveConfigInfo($info);
    }

    public static function lastSync(): void
    {
        self::save('last_synced', date('d.m.Y H:i'));
    }

    public static function lastCheck(): void
    {
        self::save('last_checked', date('d.m.Y H:i'));
    }

    public static function lastBuild(): void
    {
        self::save('last_build', date('d.m.Y H:i'));
    }

    public static function selectedProject($project): void
    {
        self::save('current_project', $project);
    }

    public static function selectedVersion($str): void
    {
        self::save('build_version', $str);
    }
}
