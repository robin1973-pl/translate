<?php
// scratch/full_cleanup.php — ONE-TIME cleanup script
// Run on server: php full_cleanup.php
// This script:
// 1. Deletes ALL old jobs from the database
// 2. Clears legacy directories (temp/, translations/, output/, uploads/)
// 3. Resets credit logs (optional)

echo "=== INDD Translation — Full Cleanup ===\n\n";

$db = new SQLite3(__DIR__ . '/../users.db');

// 1. Show current state
$r = $db->query("SELECT COUNT(*) as cnt FROM jobs");
$jobCount = $r->fetchArray(SQLITE3_ASSOC)['cnt'];
echo "Jobs in database: $jobCount\n";

$r2 = $db->query("SELECT COUNT(*) as cnt FROM credit_logs");
$logCount = $r2->fetchArray(SQLITE3_ASSOC)['cnt'];
echo "Credit logs: $logCount\n\n";

// 2. Delete all jobs
$db->exec("DELETE FROM jobs");
echo "✅ Deleted all $jobCount jobs from database.\n";

// 3. Clear credit logs (dev/test data)
$db->exec("DELETE FROM credit_logs");
echo "✅ Deleted all $logCount credit log entries.\n";

// 4. Clean legacy directories
$legacyDirs = [
    __DIR__ . '/../temp/',
    __DIR__ . '/../translations/',
    __DIR__ . '/../output/',
    __DIR__ . '/../uploads/',
    __DIR__ . '/../workspace/',
    __DIR__ . '/../logs/',
];

foreach ($legacyDirs as $dir) {
    if (is_dir($dir)) {
        rrmdir_contents($dir);
        echo "✅ Cleaned: $dir\n";
    } else {
        echo "— Skipped (not found): $dir\n";
    }
}

// 5. Reset auto-increment for jobs
$db->exec("DELETE FROM sqlite_sequence WHERE name='jobs'");
echo "\n✅ Reset job ID sequence.\n";

echo "\n=== Cleanup complete! ===\n";
echo "Next steps:\n";
echo "  - Deploy to server\n";
echo "  - Set up CRON: 0 3 * * * php /path/to/cron_cleanup.php\n";

function rrmdir_contents($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir_full($file) : unlink($file);
    }
    foreach (glob($dir . '/.*') as $file) {
        $base = basename($file);
        if ($base === '.' || $base === '..') continue;
        is_dir($file) ? rrmdir_full($file) : unlink($file);
    }
}

function rrmdir_full($dir) {
    rrmdir_contents($dir);
    rmdir($dir);
}
