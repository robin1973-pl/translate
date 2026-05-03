<?php
// helpers/workspace.php — Per-user, per-job isolated workspace management

/**
 * Returns the base workspace path for a given user and job.
 * Structure: /workspace/{user_id}/{job_id}/
 */
function get_workspace_path(int $user_id, int $job_id): string {
    $base = dirname(__DIR__) . '/workspace/';
    return $base . $user_id . '/' . $job_id . '/';
}

/**
 * Returns the extraction directory within a workspace.
 */
function get_extract_dir(int $user_id, int $job_id): string {
    return get_workspace_path($user_id, $job_id) . 'extracted/';
}

/**
 * Returns the CSV file path within a workspace.
 */
function get_csv_path(int $user_id, int $job_id): string {
    return get_workspace_path($user_id, $job_id) . 'translated.csv';
}

/**
 * Returns the output directory within a workspace.
 */
function get_output_dir(int $user_id, int $job_id): string {
    return get_workspace_path($user_id, $job_id) . 'output/';
}

/**
 * Creates the full workspace directory structure for a job.
 * Returns the workspace base path.
 */
function ensure_workspace(int $user_id, int $job_id): string {
    $ws = get_workspace_path($user_id, $job_id);
    $dirs = [
        $ws,
        get_extract_dir($user_id, $job_id),
        get_output_dir($user_id, $job_id),
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    return $ws;
}

/**
 * Removes a workspace directory recursively.
 */
function cleanup_workspace(int $user_id, int $job_id): void {
    $ws = get_workspace_path($user_id, $job_id);
    if (is_dir($ws)) {
        rrmdir_workspace($ws);
    }
}

/**
 * Recursively removes a directory and its contents.
 */
function rrmdir_workspace(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir_workspace($file) : unlink($file);
    }
    // Also remove hidden files
    foreach (glob($dir . '/.*') as $file) {
        $base = basename($file);
        if ($base === '.' || $base === '..') continue;
        is_dir($file) ? rrmdir_workspace($file) : unlink($file);
    }
    rmdir($dir);
}

/**
 * Verifies that a job belongs to the given user.
 * Returns the job row or false.
 */
function verify_job_ownership(int $job_id, int $user_id, SQLite3 $db): array|false {
    $stmt = $db->prepare("SELECT * FROM jobs WHERE id = :jid AND user_id = :uid");
    $stmt->bindValue(':jid', $job_id, SQLITE3_INTEGER);
    $stmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    return $result ?: false;
}
