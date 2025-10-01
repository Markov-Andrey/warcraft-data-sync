<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BuildGameService
{
    protected string $mpqPath;
    protected array $childProjects;
    protected string $buildOutputPath;
    protected string $parentProjectPath;
    protected array $buildFiles;
    protected array $removeFiles;
    protected array $childUniqueFiles;

    public function __construct()
    {
        $this->mpqPath = PathService::getMpqPath();
        $this->childProjects = PathService::getChildProject();
        $this->parentProjectPath = PathService::getParentProjectPath();
        $this->buildOutputPath = PathService::getBuildOutputPath();
        $this->buildFiles = config('w3x_const.copy');
        $this->removeFiles = config('w3x_const.exceptions');
        $this->childUniqueFiles = config('w3x_child');
    }

    public function buildAll(): bool
    {
        set_time_limit(0);
        try {
            foreach ($this->childProjects as $project) {
                $this->buildProject($project);
            }

            InfoConfigService::lastBuild();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function buildProject(array $project): void
    {
        $childPath = $project['path'];
        $childKey = $project['key'];
        if (!is_dir($childPath)) {
            throw new Exception("Child project directory '$childPath' does not exist.");
        }

        $projectName = basename($childPath, '.w3x');
        $version = InfoConfigService::load('build_version');
        $mpqFileName = $version
            ? "{$this->buildOutputPath}/{$projectName}-{$version}.w3x"
            : "{$this->buildOutputPath}/{$projectName}.w3x";

        $tmpDir = storage_path('app/tmp/w3x_build_' . date('Ymd_His'));
        File::ensureDirectoryExists($tmpDir);

        $this->copyParentFiles($tmpDir);
        $this->overwriteChildFiles($childPath, $tmpDir);
        $this->removeExcludedFiles($tmpDir);
        $this->removeForeignUniqueFiles($tmpDir, $childKey);
        $this->processWtsFile($tmpDir);
        $this->createMpq($tmpDir, $mpqFileName);

        // File::deleteDirectory($tmpDir);
    }

    private function copyParentFiles(string $tmpDir): void
    {
        File::copyDirectory($this->parentProjectPath, $tmpDir);
    }

    private function overwriteChildFiles(string $childPath, string $tmpDir): void
    {
        foreach ($this->buildFiles as $name) {
            $childFile = $childPath . DIRECTORY_SEPARATOR . $name;
            $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . $name;
            if (File::exists($childFile)) {
                if (File::isDirectory($childFile)) {
                    File::copyDirectory($childFile, $tmpFile);
                } else {
                    File::copy($childFile, $tmpFile);
                }
            }
        }
    }

    private function removeExcludedFiles(string $tmpDir): void
    {
        foreach ($this->removeFiles as $name) {
            $path = $tmpDir . DIRECTORY_SEPARATOR . $name;
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                } else {
                    File::delete($path);
                }
            }
        }
    }

    protected function removeForeignUniqueFiles(string $tmpDir, string $currentKey): void
    {
        foreach ($this->childUniqueFiles as $key => $files) {
            if ($key === $currentKey) {
                continue;
            }
            foreach ($files as $name) {
                $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . $name;
                if (File::exists($tmpFile)) {
                    if (File::isDirectory($tmpFile)) {
                        File::deleteDirectory($tmpFile);
                    } else {
                        File::delete($tmpFile);
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function createMpq(string $tmpDir, string $mpqFileName): void
    {
        if (!shell_exec(escapeshellcmd("$this->mpqPath new " . escapeshellarg($mpqFileName)))) {
            File::deleteDirectory($tmpDir);
            throw new Exception("Failed to create MPQ file '$mpqFileName'.");
        }

        $this->addFilesToMpq($tmpDir, $mpqFileName);

        shell_exec(escapeshellcmd("$this->mpqPath compact " . escapeshellarg($mpqFileName)));
        shell_exec(escapeshellcmd("$this->mpqPath close " . escapeshellarg($mpqFileName)));
    }

    /**
     * Remove all [title] from wts
     */
    private function processWtsFile(string $tmpDir): void
    {
        $wtsFile = $tmpDir . DIRECTORY_SEPARATOR . 'war3map.wts';
        if (!File::exists($wtsFile)) {
            return;
        }

        $content = File::get($wtsFile);

        $newContent = preg_replace([
            '/\[[^\]]+\]\s*/',
            '/(?<=\/\/).*$/m',
        ], '', $content);

        File::put($wtsFile, $newContent);
    }

    /**
     * Add all files to MPQ
     * @throws Exception
     */
    protected function addFilesToMpq(string $projectPath, string $mpqFileName): void
    {
        foreach (File::allFiles($projectPath) as $file) {
            $relativePath = str_replace($projectPath . DIRECTORY_SEPARATOR, '', $file->getPathname());

            if (!shell_exec(escapeshellcmd("$this->mpqPath add " . escapeshellarg($mpqFileName) . " " . escapeshellarg($file->getPathname()) . " " . escapeshellarg($relativePath)))) {
                throw new Exception("Failed to add '$relativePath' to '$mpqFileName'.");
            }
        }
    }
}
