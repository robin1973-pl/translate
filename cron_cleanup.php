<?php
// cron_cleanup.php - Run this once a day via CRON
// Cleans up workspace directories and legacy dirs older than 7 days
$config = require 'config.php';

$days = 7;
$seconds = $days * 24 * 60 * 60;
$now = time();
$deleted = 0;

// --- 1. Clean new workspace directories ---
$workspaceBase = $config['workspace_dir'];
if (is_dir($workspaceBase)) {
    // Iterate /workspace/{user_id}/
    foreach (glob($workspaceBase . '*', GLOB_ONLYDIR) as $userDir) {
        // Iterate /workspace/{user_id}/{job_id}/
        foreach (glob($userDir . '/*', GLOB_ONLYDIR) as $jobDir) {
            if ($now - filemtime($jobDir) > $seconds) {
                rrmdir($jobDir);
                echo "Deleted workspace: $jobDir\n";
                $deleted++;
            }
        }
        // Remove empty user dirs
        if (count(glob($userDir . '/*')) === 0) {
            rmdir($userDir);
            echo "Removed empty user dir: $userDir\n";
        }
    }
}

// --- 2. Clean legacy directories ---
$legacyDirs = [
    $config['upload_dir'],
    $config['temp_dir'],
    $config['output_dir'],
    $config['csv_dir']
];

foreach ($legacyDirs as $dir) {
    if (!is_dir($dir)) continue;
    
    $files = glob($dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) > $seconds) {
                unlink($file);
                echo "Deleted file: $file\n";
                $deleted++;
            }
        } elseif (is_dir($file)) {
            if ($now - filemtime($file) > $seconds) {
                rrmdir($file);
                echo "Deleted dir: $file\n";
                $deleted++;
            }
        }
    }
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
    // Also handle hidden files
    foreach (glob($dir . '/.*') as $file) {
        $base = basename($file);
        if ($base === '.' || $base === '..') continue;
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
    rmdir($dir);
}

echo "Cleanup finished. Deleted $deleted items.\n";
