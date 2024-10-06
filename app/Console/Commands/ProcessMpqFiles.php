<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessMpqFiles extends Command
{
    protected $signature = 'mpq:process';
    protected $description = 'Create MPQ files from subdirectories and move their contents';

    public function handle()
    {
        $sourceDir = storage_path('app/projects');
        $mpqPath = env('MPQEDITOR_PATH');

        if (!is_dir($sourceDir)) {
            $this->error("The directory '$sourceDir' does not exist.");
            return;
        }

        $this->processDirectories($sourceDir, $mpqPath);
    }

    protected function processDirectories($dir, $mpqPath)
    {
        $subDirs = File::directories($dir);

        foreach ($subDirs as $subDir) {
            $subDirName = basename($subDir);
            $mpqFileName = storage_path("app/projects/$subDirName.w3x");

            if (!shell_exec(escapeshellcmd("$mpqPath new " . escapeshellarg($mpqFileName)))) {
                $this->error("Failed to create '$mpqFileName'.");
                continue;
            }

            $this->addFilesToMpq($subDir, $mpqFileName, $mpqPath);

            if (shell_exec(escapeshellcmd("$mpqPath compact " . escapeshellarg($mpqFileName)))) {
                $this->output->writeln("Compact '$mpqFileName' successfully.");
            } else {
                $this->error("Failed to compact '$mpqFileName'.");
            }

            if (shell_exec(escapeshellcmd("$mpqPath close " . escapeshellarg($mpqFileName)))) {
                $this->output->writeln("Closed '$mpqFileName' successfully.");
            } else {
                $this->error("Failed to close '$mpqFileName'.");
            }
        }
    }

    protected function addFilesToMpq($subDir, $mpqFileName, $mpqPath)
    {
        $files = File::allFiles($subDir);
        $baseDir = storage_path('app/projects/');
        $subDirName = basename($subDir);

        $progressBar = new ProgressBar($this->output, count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $relativePath = str_replace("$baseDir$subDirName/", '', $file->getPathname());

            if (shell_exec(escapeshellcmd("$mpqPath add " . escapeshellarg($mpqFileName) . " " . escapeshellarg($relativePath) . " " . escapeshellarg($file->getRelativePathname())))) {
                $progressBar->advance();
            } else {
                $this->error("Failed to add '$relativePath' to '$mpqFileName'.");
            }
        }

        $progressBar->finish();
        $this->output->writeln("\nProcessing completed for '$mpqFileName'.");
    }
}
