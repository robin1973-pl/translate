<?php
include 'auth.php';
$config = require 'config.php';

require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['download'];

$id = $_GET['id'] ?? '';
if (!$id) die($ui['error_no_id']);

$db = new SQLite3('users.db');
$stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id AND user_id = :uid");
$stmt->bindValue(':id', $id);
$stmt->bindValue(':uid', $_SESSION['user_id']);
$job = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$job) die($ui['error_no_access']);
if ($job['status'] !== 'completed' || !$job['output_path']) die($ui['error_not_ready']);

if (!file_exists($job['output_path'])) {
    die($ui['error_not_found']);
}

$filename = basename($job['output_path']);
// Remove time prefix if exists (translated_123456_name.ext)
$display_name = preg_replace('/^translated_\d+_/', 'translated_', $filename);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $display_name . '"');
header('Content-Length: ' . filesize($job['output_path']));
readfile($job['output_path']);
exit;
