<?php

namespace App\Services;

class PathService
{
    protected static string $configDirName = '.data-sync';
    protected static string $configFileName = 'files_config.json';

    /**
     * Получить путь к файлу конфигурации
     */
    public static function getConfigFilePath(): string
    {
        return self::getConfigDirPath() . DIRECTORY_SEPARATOR . self::$configFileName;
    }

    /**
     * Получить путь к директории конфигурации
     */
    public static function getConfigDirPath(): string
    {
        return self::getParentProjectPath() . DIRECTORY_SEPARATOR . self::$configDirName;
    }

    /**
     * Получить путь к родительскому проекту
     */
    public static function getParentProjectPath(): string
    {
        return config('w3x.parent_project');
    }

    /**
     * Получить путь к дочерним проектам
     */
    public static function getChildProject(): array
    {
        return config('w3x.child_projects');
    }
}
