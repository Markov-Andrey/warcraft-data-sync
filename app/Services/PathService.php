<?php

namespace App\Services;

class PathService
{
    protected static string $configDirName = '.data-sync';
    protected static string $configFileName = 'files_config.json';
    protected static string $configInfoName = 'info_config.json';

    public static function getProjectPath()
    {
        return config('w3x_projects.project_dir');
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
        return config('w3x_projects.parent_project');
    }

    public static function getChildProject(): array
    {
        return config('w3x_projects.child_projects');
    }

    public static function getBuildOutputPath(): string
    {
        return config('w3x_projects.build_output_path');
    }

    public static function getMpqPath(): string
    {
        return base_path('tools/MPQEditor/MPQEditor.exe');
    }
}
