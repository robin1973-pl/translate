<?php
date_default_timezone_set('Europe/Warsaw');
// process_upload.php - Unified handler for IDML, DOCX, PPTX
// Refactored: per-user, per-job isolated workspace
require_once 'auth.php';
require_once 'helpers/limits.php';
require_once 'helpers/text_utils.php';
require_once 'helpers/xml_cleaner.php';
require_once 'helpers/workspace.php';
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['upload'];
$config = require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $db = new SQLite3(__DIR__ . '/users.db');
    $limitCheck = check_user_limits($_SESSION['user_id'], $db);
    if (!$limitCheck['allowed']) {
        header("Location: dashboard.php?error=limit_reached");
        exit;
    }

    $file = $_FILES['file'];
    $uploadName = $file['name'];
    $tmpPath = $file['tmp_name'];
    $ext = strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));
    $allowed = ['idml', 'docx', 'pptx'];

    if (!in_array($ext, $allowed)) {
        die($ui['error_unsupported'] . implode(', ', $allowed));
    }

    $lang = $_POST['lang'] ?? $_GET['lang'] ?? $config['default_lang'];

    // 1. FIRST create job in DB to get a unique job_id
    $stmt = $db->prepare("INSERT INTO jobs (user_id, filename, file_path, file_type, target_lang, status) VALUES (:uid, :fname, '', :ftype, :lang, 'processing')");
    $stmt->bindValue(':uid', $_SESSION['user_id']);
    $stmt->bindValue(':fname', $uploadName);
    $stmt->bindValue(':ftype', $ext);
    $stmt->bindValue(':lang', $lang);
    $stmt->execute();
    $job_id = $db->lastInsertRowID();
    $user_id = (int)$_SESSION['user_id'];

    // 2. Create isolated workspace for this user+job
    $ws = ensure_workspace($user_id, $job_id);
    $extractDir = get_extract_dir($user_id, $job_id);
    $csvFile = get_csv_path($user_id, $job_id);

    // 3. Save uploaded file INTO the workspace (not global uploads/)
    $uploadDir = $config['upload_dir'];
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $zipPath = $uploadDir . $user_id . '_' . $job_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $uploadName);
    if (!move_uploaded_file($tmpPath, $zipPath)) {
        die($ui['error_save']);
    }

    // Update job with actual file path
    $stmt2 = $db->prepare("UPDATE jobs SET file_path = :fpath WHERE id = :jid");
    $stmt2->bindValue(':fpath', $zipPath);
    $stmt2->bindValue(':jid', $job_id);
    $stmt2->execute();

    // Deduct credit
    deduct_credit($user_id, $db);

    // 4. Extract into ISOLATED workspace (no rrmdir on global dirs!)
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractDir);
        $zip->close();
    } else {
        die($ui['error_extract']);
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

    // 5. Save CSV into ISOLATED workspace
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, ['FileName', 'ContentIndex', 'OriginalText', 'TranslatedText']);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    // 6. Redirect — job_id is the key to the isolated workspace
    header("Location: translate_ui.php?lang=$lang&job_id=$job_id&file=" . urlencode($zipPath) . "&original_idml=" . urlencode($uploadName));
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
