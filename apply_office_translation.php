<?php // apply_office_translation.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require 'config.php';

if (isset($_GET['original_file'])) {
    $originalName = $_GET['original_file'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    
    $uploadDir = $config['upload_dir'];
    $tempDir = $config['temp_dir'] . 'rebuild_' . time() . '/';
    $outputDir = $config['output_dir'];
    $csvFile = $config['csv_dir'] . 'translated.csv';

    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

    // 1. Rozpakuj oryginał do temp
    $zip = new ZipArchive;
    if ($zip->open($uploadDir . $originalName) === TRUE) {
        $zip->extractTo($tempDir);
        $zip->close();
    } else {
        die("Błąd otwierania oryginału.");
    }

    // 2. Wczytaj tłumaczenia z CSV
    $translations = [];
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle)) !== FALSE) {
            // [FileName, ContentIndex, Original, Translated]
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
            $dom->formatOutput = false;
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

    // 4. Zapakuj z powrotem
    $outputFile = $outputDir . 'translated_' . $originalName;
    $zip = new ZipArchive;
    if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(realpath($tempDir)) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    // 5. Pobieranie
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    exit;
}
?>
