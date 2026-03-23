<?php

namespace App\Services;

class MapConverterService
{
    /**
     * Проверяет, нужно ли конвертировать файл в JSON.
     *
     * @param string $sourcePath Путь к исходному файлу.
     * @param string $jsonPath Путь к JSON файлу.
     * @return bool
     */
    public static function shouldConvert(string $sourcePath, string $jsonPath): bool
    {
        return !file_exists($jsonPath) || filemtime($sourcePath) > filemtime($jsonPath);
    }

    /**
     * Конвертирует файл в JSON формат.
     *
     * @param string $inputPath Путь к исходному файлу.
     * @param string $outputPath Путь к JSON файлу.
     */
    public static function convertToJson(string $inputPath, string $outputPath): void
    {
        $command = "npx patchwork-mapconverter war2json \"$inputPath\" \"$outputPath\"";

        shell_exec($command);
    }

    public static function convertToWar(string $inputPath, string $outputPath): string
    {
        $command = "npx patchwork-mapconverter json2war \"$inputPath\" \"$outputPath\" 2>&1";

        return shell_exec($command) ?? '';
    }
}
