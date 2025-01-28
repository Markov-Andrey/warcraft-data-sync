<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigService
{
    protected $configFileName = '.config.json';

    // Инициализация конфигурационного файла
    public function initializeConfig(): void
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];

        $configPath = $parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
        if (!File::exists($configPath)) {
            File::put($configPath, '');
        }
    }

    // Загрузка конфигурации
    public function loadConfig()
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];

        $configPath = $parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
        if (File::exists($configPath)) {
            $content = File::get($configPath);
            if (!empty($content)) {
                return json_decode($content, true);
            }
        }

        return [];
    }

    public function updateParameters($parentProjectPath, $data): void
    {
        // Загружаем текущий конфиг
        $configData = $this->loadConfig($parentProjectPath);

        // Обновляем данные для директорий
        foreach ($data['directories'] ?? [] as $directory => $copy) {
            if (isset($configData['directories'][$directory])) {
                $configData['directories'][$directory]['copy'] = (bool) $copy;
            }
        }

        // Обновляем данные для файлов
        foreach ($data['files'] ?? [] as $file => $copy) {
            if (isset($configData['files'][$file])) {
                $configData['files'][$file]['copy'] = (bool) $copy;
            }
        }

        // Сохраняем обновленный конфиг
        $this->saveConfig($parentProjectPath, $configData);
    }

    // Синхронизация конфигурации с файловой системой
    public function syncConfig(): void
    {
        $config = config('w3x');
        $parentProjectPath = $config['parent_project'];

        // Собираем все директории и файлы с параметром 'copy'
        $directories = $this->getDirectoriesWithCopy($parentProjectPath);
        $files = $this->getFilesWithCopy($parentProjectPath);

        // Получаем текущие данные конфига
        $configData = $this->loadConfig($parentProjectPath);

        // Добавляем или обновляем директории в конфиге
        foreach ($directories as $dirName => $dirData) {
            if (!isset($configData['directories'][$dirName])) {
                $configData['directories'][$dirName] = $dirData;
            } else {
                $configData['directories'][$dirName] = array_merge($configData['directories'][$dirName], $dirData);
            }
        }

        // Добавляем или обновляем файлы в конфиге
        foreach ($files as $fileName => $fileData) {
            if (!isset($configData['files'][$fileName])) {
                $configData['files'][$fileName] = $fileData;
            } else {
                $configData['files'][$fileName] = array_merge($configData['files'][$fileName], $fileData);
            }
        }

        // Удаляем из конфига элементы, которые отсутствуют в файловой системе
        $configData['directories'] = array_filter($configData['directories'], function ($dirName) use ($directories) {
            // Преобразуем путь в строку и проверяем, существует ли он в новых директориях
            return in_array($dirName, array_map(fn($dir) => basename($dir), array_keys($directories)));
        }, ARRAY_FILTER_USE_KEY);

        $configData['files'] = array_filter($configData['files'], function ($fileName) use ($files) {
            // Преобразуем путь в строку и проверяем, существует ли он в новых файлах
            return in_array($fileName, array_map(fn($file) => basename($file), array_keys($files)));
        }, ARRAY_FILTER_USE_KEY);

        // Сохраняем обновленный конфиг
        $this->saveConfig($parentProjectPath, $configData);
    }

    private function getDirectoriesWithCopy(string $path, array $directories = []): array
    {
        if (File::isDirectory($path)) {
            // Собираем все директории
            foreach (File::directories($path) as $dir) {
                $dirName = basename($dir);
                // Добавляем в массив с параметром 'copy' (по умолчанию false)
                $directories[$dirName] = ['copy' => false];
                // Рекурсивно обрабатываем вложенные директории
                $directories = $this->getDirectoriesWithCopy($dir, $directories);
            }
        }

        return $directories;
    }

    private function getFilesWithCopy(string $path, array $files = []): array
    {
        if (File::isDirectory($path)) {
            // Собираем все файлы
            foreach (File::files($path) as $file) {
                $fileName = $file->getFilename();
                // Добавляем в массив с параметром 'copy' (по умолчанию false)
                $files[$fileName] = ['copy' => false];
            }
        }

        return $files;
    }

    // Сохранение конфигурации
    public function saveConfig($parentProjectPath, $configData): void
    {
        $configPath = $parentProjectPath . DIRECTORY_SEPARATOR . $this->configFileName;
        File::put($configPath, json_encode($configData, JSON_PRETTY_PRINT));
    }
}


