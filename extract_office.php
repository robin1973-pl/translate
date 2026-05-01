<?php // extract_office.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require 'config.php';

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['office_file'])) {
    $lang = $_POST['lang'] ?? $config['default_lang'];
    $uploadName = $_FILES['office_file']['name'];
    $tmpPath = $_FILES['office_file']['tmp_name'];
    $ext = strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));

    $basename = pathinfo($uploadName, PATHINFO_FILENAME);
    $uploadDir = $config['upload_dir'];
    $extractDir = $config['temp_dir'];
    $csvFile = $config['csv_dir'] . 'translated.csv';

    rrmdir($extractDir);
    if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
    if (!is_dir($config['csv_dir'])) mkdir($config['csv_dir'], 0777, true);

    $zipPath = $uploadDir . $basename . '.' . $ext;
    move_uploaded_file($tmpPath, $zipPath);

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
            $xmlString = file_get_contents($xmlFile);
            $dom = new DOMDocument();
            $dom->loadXML($xmlString);
            $texts = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
            foreach ($texts as $i => $node) {
                $rows[] = ['word/document.xml', $i, $node->nodeValue, ''];
            }
        }
    } elseif ($ext === 'pptx') {
        $slideFiles = glob($extractDir . 'ppt/slides/slide*.xml');
        foreach ($slideFiles as $file) {
            $xmlString = file_get_contents($file);
            $dom = new DOMDocument();
            $dom->loadXML($xmlString);
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

    header("Location: translate_ui.php?lang=$lang&original_file=" . urlencode($uploadName));
    exit;
}
?>
