<?php

namespace App\Services;

class GitService
{
    public static function commit($commit_message): array
    {
        $repoPath = PathService::getProjectPath();

        chdir($repoPath);
        exec('git add .');
        exec("git commit -m " . escapeshellarg($commit_message), $output, $returnVar);
        exec('git push origin master', $pushOutput, $pushReturnVar);
        if ($returnVar !== 0) {
            return ['success' => false, 'message' => 'Error during commit: ' . implode("\n", $output)];
        }
        if ($pushReturnVar !== 0) {
            return ['success' => false, 'message' => 'Error during push: ' . implode("\n", $pushOutput)];
        }

        return ['success' => true, 'message' => 'Commit and push successful'];
    }
}

