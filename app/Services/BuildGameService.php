<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BuildGameService
{
    protected string $mpqPath;
    protected array $childProjects;
    protected string $buildOutputPath;

    public function __construct()
    {
        $this->mpqPath = config('w3x_mpq.mpqeditor_path');
        $this->childProjects = config('w3x.child_projects');
        $this->buildOutputPath = config('w3x.build_output_path');
    }

    public function buildAll()
    {
        set_time_limit(1000);

        try {
            foreach ($this->childProjects as $project) {
                $this->buildProject($project['path']);
            }

            InfoConfigService::lastBuild();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function buildProject(string $projectPath): void
    {
        if (!is_dir($projectPath)) {
            throw new \Exception("Project directory '$projectPath' does not exist.");
        }

        $projectName = basename($projectPath, '.w3x');
        $mpqFileName = "{$this->buildOutputPath}/{$projectName}.w3x";

        if (!shell_exec(escapeshellcmd("$this->mpqPath new " . escapeshellarg($mpqFileName)))) {
            throw new \Exception("Failed to create MPQ file '$mpqFileName'.");
        }

        $this->addFilesToMpq($projectPath, $mpqFileName);

        shell_exec(escapeshellcmd("$this->mpqPath compact " . escapeshellarg($mpqFileName)));
        shell_exec(escapeshellcmd("$this->mpqPath close " . escapeshellarg($mpqFileName)));
    }

    protected function addFilesToMpq(string $projectPath, string $mpqFileName): void
    {
        foreach (File::allFiles(dirname($projectPath)) as $file) {
            $relativePath = str_replace(dirname($projectPath) . '/', '', $file->getPathname());

            if (!shell_exec(escapeshellcmd("$this->mpqPath add " . escapeshellarg($mpqFileName) . " " . escapeshellarg($relativePath) . " " . escapeshellarg($file->getRelativePathname())))) {
                throw new \Exception("Failed to add '$relativePath' to '$mpqFileName'.");
            }
        }
    }
}
