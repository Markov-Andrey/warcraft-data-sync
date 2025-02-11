<?php

namespace App\Services;

class PathService
{
    protected static string $configDirName = '.data-sync';
    protected static string $configFileName = 'files_config.json';
    protected static string $configInfoName = 'info_config.json';

    public static function getProjectPath()
    {
        return env('PROJECT');
    }
    public static function getConfigFilePath(): string
    {
        return self::getConfigDirPath() . DIRECTORY_SEPARATOR . self::$configFileName;
    }

    public static function getconfigInfoPath(): string
    {
        return self::getConfigDirPath() . DIRECTORY_SEPARATOR . self::$configInfoName;
    }

    public static function getConfigDirPath(): string
    {
        return self::getParentProjectPath() . DIRECTORY_SEPARATOR . self::$configDirName;
    }

    public static function getParentProjectPath(): string
    {
        return env('PROJECT');
    }

    public static function getChildProject(): array
    {
        return json_decode(env('CHILD_PROJECTS'), true);
    }
}
