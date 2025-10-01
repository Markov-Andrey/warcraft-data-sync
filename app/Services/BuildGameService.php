<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;

class BuildGameService
{
    protected string $mpqPath;
    protected array $childProjects;
    protected string $buildOutputPath;

    public function __construct()
    {
        $this->mpqPath = PathService::getMpqPath();
        $this->childProjects = PathService::getChildProject();
        $this->buildOutputPath = PathService::getBuildOutputPath();
    }

    public function buildAll(): bool
    {
        set_time_limit(0);

        try {
            foreach ($this->childProjects as $project) {
                $this->buildProject($project['path']);
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
    public function buildProject(string $projectPath): void
    {
        if (!is_dir($projectPath)) {
            throw new Exception("Project directory '$projectPath' does not exist.");
        }

        $projectName = basename($projectPath, '.w3x');
        $version = InfoConfigService::load('build_version');
        $mpqFileName = $version
            ? "{$this->buildOutputPath}/{$projectName}-{$version}.w3x"
            : "{$this->buildOutputPath}/{$projectName}.w3x";

        $tmpDir = storage_path('tmp/w3x_build_' . uniqid());
        File::ensureDirectoryExists($tmpDir);

        File::copyDirectory($projectPath, $tmpDir);

        $wtsFile = $tmpDir . DIRECTORY_SEPARATOR . 'war3map.wts';
        if (File::exists($wtsFile)) {
            File::put($wtsFile, $this->processWts($wtsFile));
        }

        if (!shell_exec(escapeshellcmd("$this->mpqPath new " . escapeshellarg($mpqFileName)))) {
            File::deleteDirectory($tmpDir);
            throw new Exception("Failed to create MPQ file '$mpqFileName'.");
        }

        $this->addFilesToMpq($tmpDir, $mpqFileName);

        shell_exec(escapeshellcmd("$this->mpqPath compact " . escapeshellarg($mpqFileName)));
        shell_exec(escapeshellcmd("$this->mpqPath close " . escapeshellarg($mpqFileName)));

        File::deleteDirectory($tmpDir);
    }

    /**
     * Remove all [title] from wts
     */
    protected function processWts(string $filePath): string
    {
        $content = File::get($filePath);
        return preg_replace('/^\s*\[[^]]*]\s*/', '', $content);
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
