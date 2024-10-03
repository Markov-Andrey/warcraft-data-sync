<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class BuildService
{
    /**
     * Метод для копирования файлов и директорий из донора в проекты
     */
    public function copyW3xConfig()
    {
        // Путь донора
        $donorPath = storage_path('app/donor');

        // Путь к проектам
        $projectsPath = storage_path('app/projects');

        // Получаем конфигурацию файлов и директорий
        $config = config('w3x');

        // Копируем файлы из донора в каждый проект
        foreach (File::directories($projectsPath) as $projectDirectory) {
            $this->removeFiles($projectDirectory, $config['files']);

            // Копируем файлы
            foreach ($config['files'] as $fileName => $fileOptions) {
                if ($fileOptions['copy']) {
                    if ($fileName == 'war3map.j') {
                        // TODO special logic for maps
                        continue;
                    }
                    if ($fileName == 'war3map.wts') {
                        // special logic for string remover
                        $this->processWtsFile($donorPath, $projectDirectory, $fileName);
                        continue;
                    }
                    $this->copyFile($donorPath, $projectDirectory, $fileName);
                }
            }

            // Копируем директории рекурсивно
            foreach ($config['directories'] as $directoryName => $directoryOptions) {
                if ($directoryOptions['copy']) {
                    $this->copyDirectory($donorPath, $projectDirectory, $directoryName);
                }
            }
        }
    }

    /**
     * Копирует файл с заменой из донора в проект
     *
     * @param string $sourceDir Путь до исходного файла
     * @param string $targetDir Путь до целевого проекта
     * @param string $fileName Имя файла для копирования
     */
    protected function copyFile($sourceDir, $targetDir, $fileName)
    {
        $sourcePath = $sourceDir . '/' . $fileName;
        $targetPath = $targetDir . '/' . $fileName;

        if (File::exists($sourcePath)) {
            File::copy($sourcePath, $targetPath);
            echo "Copied file: $fileName to $targetDir\n";
        } else {
            echo "File $fileName not found in donor directory.\n";
        }
    }

    /**
     * Рекурсивно копирует директорию с заменой файлов
     *
     * @param string $sourceDir Путь до исходной директории
     * @param string $targetDir Путь до целевого проекта
     * @param string $directoryName Имя директории для копирования
     */
    protected function copyDirectory(string $sourceDir, string $targetDir, string $directoryName)
    {
        $sourcePath = $sourceDir . '/' . $directoryName;
        $targetPath = $targetDir . '/' . $directoryName;

        if (File::exists($sourcePath)) {
            // Удаление целевой директории перед копированием, если она существует
            if (File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
                echo "Deleted existing directory: $targetPath\n";
            }

            // Рекурсивное копирование директории
            File::copyDirectory($sourcePath, $targetPath);
            echo "Copied directory: $directoryName to $targetDir\n";
        } else {
            echo "Directory $directoryName not found in donor directory.\n";
        }
    }

    protected function removeFiles($projectDirectory, $listFiles): void
    {
        // Получаем список существующих файлов в проекте
        $existingFiles = File::files($projectDirectory);  // Получаем только файлы
        $configFiles = array_keys($listFiles);      // Файлы из конфигурации

        // Удаление лишних файлов, которых нет в конфигурации
        foreach ($existingFiles as $existingFile) {
            $fileName = basename($existingFile);
            if (!in_array($fileName, $configFiles)) {
                File::delete($existingFile);
                echo "Deleted extra file: $fileName from $projectDirectory\n";
            }
        }
    }

    protected function processWtsFile($donorPath, $projectDirectory, $fileName)
    {
        // Путь к исходному файлу, временной копии и целевому файлу
        $sourceFile = $donorPath . '/' . $fileName;
        $tempFile = $donorPath . '/' . $fileName . '.tmp';
        $targetFile = $projectDirectory . '/' . $fileName;

        // Убедимся, что файл существует
        if (!File::exists($sourceFile)) {
            echo "File $sourceFile not found.\n";
            return;
        }

        // Создаем временную копию файла
        File::copy($sourceFile, $tempFile);

        // Загружаем конфиг с заменами строк
        $wtsConfig = config('wts.remove_string');  // Массив строк для замены

        // Читаем содержимое временного файла
        $content = File::get($tempFile);

        // Производим замену строк на пустые
        $content = preg_replace('/\[[^\]]*\]\s*/', '', $content);

        // Записываем измененный контент обратно в временный файл
        File::put($tempFile, $content);

        // Копируем временный файл в целевую директорию
        File::copy($tempFile, $targetFile);

        // Удаляем временный файл
        File::delete($tempFile);

        echo "Processed war3map.wts and removed specified strings.\n";
    }
}
