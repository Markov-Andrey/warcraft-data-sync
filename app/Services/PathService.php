<?php

namespace App\Services;

class PathService
{
    protected static string $configFileName = '.files_config.json';

    /**
     * Получить имя файла конфигурации
     */
    public static function getConfigFileName(): string
    {
        return self::$configFileName;
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

    /**
     * Получить путь к файлу конфигурации
     */
    public static function getConfigPath(): string
    {
        return self::getParentProjectPath() . DIRECTORY_SEPARATOR . self::$configFileName;
    }

    /**
     * Получить путь к файлу в дочернем проекте
     *
     * @param string $childProjectPath Путь к дочернему проекту
     * @param string $itemPath Относительный путь к файлу
     */
    public static function getTargetPath(string $childProjectPath, string $itemPath): string
    {
        return rtrim($childProjectPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($itemPath, DIRECTORY_SEPARATOR);
    }
}
