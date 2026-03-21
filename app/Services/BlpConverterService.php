<?php

namespace App\Services;

use App\Services\Blp\BLPImage;
use Exception;
use ValueError;

class BlpConverterService
{
    protected string $sourcePath;
    protected string $targetPath;

    public function __construct(string $sourcePath = null, string $targetPath = null)
    {
        // пути по умолчанию
        $this->sourcePath = $sourcePath ?? storage_path('app/public/blp');
        $this->targetPath = $targetPath ?? storage_path('app/public/png');

    }

    public function convert(string $filename, string $format = 'png'): string
    {
        $sourceFile = $this->sourcePath . '/' . $filename;

        if (!file_exists($sourceFile)) {
            throw new Exception("Файл {$sourceFile} не найден");
        }

        $blp = new BLPImage($sourceFile);
        $image = $blp->image();
        $image->setImageFormat($format);

        $outName = pathinfo($filename, PATHINFO_FILENAME) . '.' . $format;
        $outPath = $this->targetPath . '/' . $outName;

        // создаём директорию если нет
        if (!is_dir($this->targetPath)) {
            mkdir($this->targetPath, 0777, true);
        }

        $image->writeImage($outPath);
        $blp->close();

        return $outPath;
    }

    /**
     * Конвертирует BLP в PNG в storage, сохраняет структуру, учитывает обновления
     * @param string $absoluteBlpPath - абсолютный путь к .blp файлу
     * @param string $format - формат (по умолчанию png)
     * @return string|null - путь к PNG в storage
     */
    public function convertToStorage(string $absoluteBlpPath, string $format = 'png'): ?string
    {
        // приводим слэши к одному виду
        $absoluteBlpPath = str_replace('\\', '/', $absoluteBlpPath);

        // проверка существования файла и что он не пустой
        if (!file_exists($absoluteBlpPath) || filesize($absoluteBlpPath) === 0) {
            return null; // пропускаем пустые иконки
        }

        // путь проекта
        $projectPath = str_replace('\\', '/', PathService::getParentProjectPath());
        $relativePath = ltrim(str_replace($projectPath, '', $absoluteBlpPath), '/');

        // формируем путь для PNG в storage/app/public/png
        $relativeDir = pathinfo($relativePath, PATHINFO_DIRNAME);
        $filenameNoExt = pathinfo($relativePath, PATHINFO_FILENAME);
        $pngDir = $this->targetPath . '/' . $relativeDir;
        $pngPath = $pngDir . '/' . $filenameNoExt . '.' . $format;

        if (!is_dir($pngDir)) {
            mkdir($pngDir, 0777, true);
        }

        try {
            if (!file_exists($pngPath) || filemtime($absoluteBlpPath) > filemtime($pngPath)) {
                $blp = new BLPImage($absoluteBlpPath);
                $image = $blp->image();

                if ($image->getImageWidth() > 0 && $image->getImageHeight() > 0) {
                    $image->setImageFormat($format);
                    $image->writeImage($pngPath);
                } else {
                    $pngPath = null;
                }

                $blp->close();
            }
        } catch (Exception | ValueError $e) {
            $pngPath = null;
        }

        if ($pngPath) {
            return str_replace($this->targetPath . '/', '', $pngPath);
        }

        return null;
    }
}
