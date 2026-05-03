<?php // apply_office_translation.php
include 'auth.php';
require_once 'helpers/workspace.php';
$config = require 'config.php';

require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['translate'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_path'])) {
    $filePath = $_POST['file_path']; // Full path on server
    if (!file_exists($filePath)) {
        die($ui['error_source_not_found']);
    }
    
    $originalName = $_POST['original_idml'] ?? basename($filePath);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $job_id = (int)($_POST['job_id'] ?? 0);
    $user_id = (int)$_SESSION['user_id'];

    // Workspace-based paths
    if ($job_id > 0) {
        $tempDir = get_workspace_path($user_id, $job_id) . 'rebuild_office/';
        $outputDir = get_output_dir($user_id, $job_id);
        $csvFile = get_csv_path($user_id, $job_id);
    } else {
        // Legacy fallback
        $tempDir = $config['temp_dir'] . 'rebuild_' . uniqid() . '/';
        $outputDir = $config['output_dir'];
        $csvFile = $config['csv_dir'] . 'translated.csv';
    }

    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

    // 1. Rozpakuj oryginał do temp
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        $zip->extractTo($tempDir);
        $zip->close();
    } else {
        die($ui['error_zip_open']);
    }

    // 2. Wczytaj tłumaczenia z CSV
    $translations = [];
    if (file_exists($csvFile) && ($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle)) !== FALSE) {
            $translations[$data[0]][$data[1]] = $data[3];
        }
        fclose($handle);
    }

    // 3. Podmień teksty w plikach XML
    foreach ($translations as $xmlRelPath => $texts) {
        $fullPath = $tempDir . ($ext === 'pptx' && strpos($xmlRelPath, 'slide') !== false ? 'ppt/slides/' : '') . $xmlRelPath;
        if (file_exists($fullPath)) {
            $xmlString = file_get_contents($fullPath);
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = true;
            $dom->loadXML($xmlString);

            $ns = ($ext === 'docx') ? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main' : 'http://schemas.openxmlformats.org/drawingml/2006/main';
            $nodes = $dom->getElementsByTagNameNS($ns, 't');

            foreach ($texts as $index => $translated) {
                if ($translated !== '' && $nodes->item($index)) {
                    $nodes->item($index)->nodeValue = htmlspecialchars($translated, ENT_XML1);
                }
            }
            $dom->save($fullPath);
        }
    }

    // 4. Zapakuj z powrotem — unique filename
    $outputFile = $outputDir . 'translated_' . uniqid() . '_' . $originalName;
    $zip = new ZipArchive;
    if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePathOnDisk = $file->getRealPath();
                $relativePath = substr($filePathOnDisk, strlen(realpath($tempDir)) + 1);
                $zip->addFile($filePathOnDisk, $relativePath);
            }
        }
        $zip->close();
    }

    // 4.5. Aktualizacja bazy danych (JOBS)
    if ($job_id) {
        $db = new SQLite3('users.db');
        $stmt = $db->prepare("UPDATE jobs SET status = 'completed', output_path = :path WHERE id = :id AND user_id = :uid");
        $stmt->bindValue(':path', $outputFile);
        $stmt->bindValue(':id', $job_id);
        $stmt->bindValue(':uid', $user_id);
        $stmt->execute();
    }

    // 5. Pobieranie
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="translated_' . $originalName . '"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    exit;
}
