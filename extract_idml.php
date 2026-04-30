<?php // extract_idml.php

// ✅ DODAJ DEBUGOWANIE NA POCZĄTKU
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once __DIR__ . '/helpers/xml_cleaner.php';
require_once __DIR__ . '/helpers/format_helpers.php';
$config = require 'config.php';



// 🔧 Funkcja usuwająca cały katalog (rekurencyjnie)
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
}

// 🔧 Funkcja czyszcząca stare pliki w folderze
function cleanOldFiles($dir, $maxAgeSeconds = 3600) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        if (is_file($file) && (time() - filemtime($file) > $maxAgeSeconds)) {
            unlink($file);
        }
    }
}

// ---------------------------
// Główna logika
// ---------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['idml'])) {
    $lang = $_POST['lang'] ?? $config['default_lang'];
    $uploadName = $_FILES['idml']['name'];
    $tmpPath = $_FILES['idml']['tmp_name'];

    $basename = pathinfo($uploadName, PATHINFO_FILENAME);
    $uploadDir = $config['upload_dir'];
    $extractDir = $config['temp_dir'];
    $csvFile = $config['csv_dir'] . 'translated.csv';

    // Czyszczenie folderów roboczych
    rrmdir($extractDir);
    rrmdir($config['csv_dir']);
    if (!is_dir($extractDir)) mkdir($extractDir, 0777, true);
    if (!is_dir($config['csv_dir'])) mkdir($config['csv_dir'], 0777, true);
    
    // Czyszczenie starych plików (starszych niż 1h) w output i uploads
    cleanOldFiles($config['output_dir'], 3600);
    cleanOldFiles($config['upload_dir'], 3600);

    // Upewnij się, że folder uploads istnieje
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $zipPath = $uploadDir . $basename . '.idml';
    move_uploaded_file($tmpPath, $zipPath);

    // Rozpakuj IDML
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractDir);
        $zip->close();
    } else {
        die("Nie udało się rozpakować pliku IDML.");
    }

    // Przetwarzanie plików Story_*.xml
    $storyDir = $extractDir . 'Stories/';
    $files = glob($storyDir . 'Story_*.xml');
    $rows = [];

    foreach ($files as $file) {
        $xmlString = file_get_contents($file);
        
        // 💡 0️⃣ WYCZYŚĆ I SCAL XML (ZAWIERA USUWANIE HYPERLINKÓW, SCALANIE I SPACJE)
        $xmlString = cleanIDMLXML($xmlString);
    
        // 💡 3️⃣ Zapisz poprawioną wersję XML z powrotem
        file_put_contents($file, $xmlString);
    
        // 💡 4️⃣ Wczytaj do DOM, by pobrać teksty
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        $dom->loadXML($xmlString);
    
        $contents = $dom->getElementsByTagName('Content');
    
        foreach ($contents as $i => $node) {
            $text = $node->nodeValue;
            $protectedText = protectSpacesForTranslation($text);
            $rows[] = [basename($file), $i, $protectedText, ''];
        }
    }

    // Zapisz CSV
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, ['FileName', 'ContentIndex', 'OriginalText', 'TranslatedText']);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    // Przekieruj do interfejsu tłumaczeń
    header("Location: translate_ui.php?lang=$lang&original_idml=" . urlencode($uploadName));
    exit;

}

echo "Nieprawidłowe wywołanie.";