<?php include 'auth.php'; // extract_office.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require 'config.php';
require_once 'helpers/limits.php';
require_once 'helpers/text_utils.php';
require_once 'helpers/office_utils.php';

$db = new SQLite3(__DIR__ . '/users.db');
if (isset($_SESSION['user_id'])) {
    $limitCheck = check_user_limits($_SESSION['user_id'], $db);
    if (!$limitCheck['allowed']) {
        header("Location: dashboard.php?error=limit_reached");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_FILES['office_file']) || isset($_FILES['file']))) {
    $fileObj = $_FILES['office_file'] ?? $_FILES['file'];
    $lang = $_POST['lang'] ?? $config['default_lang'];
    $uploadName = $fileObj['name'];
    $tmpPath = $fileObj['tmp_name'];
    $ext = strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));

    $basename = pathinfo($uploadName, PATHINFO_FILENAME);
    $uploadDir = $config['upload_dir'];
    $extractDir = $config['temp_dir'];
    $csvFile = $config['csv_dir'] . 'translated.csv';

    rrmdir($extractDir);
    if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
    if (!is_dir($config['csv_dir'])) mkdir($config['csv_dir'], 0777, true);

    $zipPath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $uploadName);
    move_uploaded_file($tmpPath, $zipPath);

    // 📝 ZAPISZ W HISTORII (JOBS)
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("INSERT INTO jobs (user_id, filename, file_type, target_lang, status) VALUES (:uid, :fname, :ftype, :lang, 'uploaded')");
        $stmt->bindValue(':uid', $_SESSION['user_id']);
        $stmt->bindValue(':fname', $uploadName);
        $stmt->bindValue(':ftype', $ext);
        $stmt->bindValue(':lang', $lang);
        $stmt->execute();
        deduct_credit($_SESSION['user_id'], $db);
    }

    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractDir);
        $zip->close();
    } else {
        die("Nie udało się rozpakować pliku.");
    }

    $rows = [];
    if ($ext === 'docx') {
        $xmlFile = $extractDir . 'word/document.xml';
        if (file_exists($xmlFile)) {
            $xmlContent = file_get_contents($xmlFile);
            
            // CLEANING DOCX XML - Remove proof errors and bookmarks that split text
            $xmlContent = preg_replace('/<w:proofErr[^>]*\/>/u', '', $xmlContent);
            $xmlContent = preg_replace('/<w:bookmarkStart[^>]*\/>/u', '', $xmlContent);
            $xmlContent = preg_replace('/<w:bookmarkEnd[^>]*\/>/u', '', $xmlContent);
            
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = true;
            $dom->loadXML($xmlContent);
            
            // Merge runs
            $dom = cleanDocxXML($dom);
            file_put_contents($xmlFile, $dom->saveXML());
            
            $texts = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
            foreach ($texts as $i => $node) {
                $rows[] = ['word/document.xml', $i, $node->nodeValue, ''];
            }
        }
    } elseif ($ext === 'pptx') {
        $slideFiles = glob($extractDir . 'ppt/slides/slide*.xml');
        foreach ($slideFiles as $file) {
            $xmlContent = file_get_contents($file);
            $dom = new DOMDocument();
            $dom->loadXML($xmlContent);
            
            $texts = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 't');
            foreach ($texts as $i => $node) {
                $rows[] = [basename($file), $i, $node->nodeValue, ''];
            }
        }
    }

    $fp = fopen($csvFile, 'w');
    fputcsv($fp, ['FileName', 'ContentIndex', 'OriginalText', 'TranslatedText']);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    $job_id = $db->lastInsertRowID();
    header("Location: translate_ui.php?lang=$lang&job_id=$job_id&file=" . urlencode($zipPath));
    exit;
}
