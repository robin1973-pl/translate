<?php
date_default_timezone_set('Europe/Warsaw');
// process_resume.php - Resume translation of an existing job
// Refactored: per-user, per-job isolated workspace
require_once 'auth.php';
require_once 'helpers/text_utils.php';
require_once 'helpers/xml_cleaner.php';
require_once 'helpers/workspace.php';
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['translate'];
$config = require 'config.php';

$job_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
if (!$job_id) die($ui['error_no_csv']);

$db = new SQLite3(__DIR__ . '/users.db');
$job = verify_job_ownership($job_id, $user_id, $db);
if (!$job) die($ui['error_source_not_found']);

$zipPath = $job['file_path'];
if (!$zipPath || !file_exists($zipPath)) {
    die($ui['error_source_not_found']);
}

$ext = $job['file_type'];
$lang = $job['target_lang'];

// Use isolated workspace — NO global rrmdir!
$ws = ensure_workspace($user_id, $job_id);
$extractDir = get_extract_dir($user_id, $job_id);
$csvFile = get_csv_path($user_id, $job_id);

// Clean only THIS job's extract dir if re-extracting
if (is_dir($extractDir)) {
    rrmdir_workspace($extractDir);
    mkdir($extractDir, 0777, true);
}

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {
    $zip->extractTo($extractDir);
    $zip->close();
} else {
    die($ui['error_zip_open']);
}

$rows = [];
if ($ext === 'idml') {
    $storyDir = $extractDir . 'Stories/';
    $files = glob($storyDir . 'Story_*.xml');
    foreach ($files as $f) {
        $xmlString = file_get_contents($f);
        $xmlString = cleanIDMLXML($xmlString);
        file_put_contents($f, $xmlString);
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xmlString);
        $contents = $dom->getElementsByTagName('Content');
        foreach ($contents as $i => $node) {
            $rows[] = [basename($f), $i, protectSpacesForTranslation($node->nodeValue), ''];
        }
    }
} elseif ($ext === 'docx') {
    $xmlFile = $extractDir . 'word/document.xml';
    if (file_exists($xmlFile)) {
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $texts = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
        foreach ($texts as $i => $node) {
            $rows[] = ['word/document.xml', $i, $node->nodeValue, ''];
        }
    }
} elseif ($ext === 'pptx') {
    $slideFiles = glob($extractDir . 'ppt/slides/slide*.xml');
    foreach ($slideFiles as $f) {
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($f));
        $texts = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 't');
        foreach ($texts as $i => $node) {
            $rows[] = [basename($f), $i, $node->nodeValue, ''];
        }
    }
}

$fp = fopen($csvFile, 'w');
fputcsv($fp, ['FileName', 'ContentIndex', 'OriginalText', 'TranslatedText']);
foreach ($rows as $row) fputcsv($fp, $row);
fclose($fp);

header("Location: translate_ui.php?lang=$lang&job_id=$job_id&file=" . urlencode($zipPath) . "&original_idml=" . urlencode($job['filename']));
exit;
