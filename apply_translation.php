<?php
// apply_translation.php – zapis i wygenerowanie IDML z ochroną spacji

session_start();
$config = require 'config.php';
require_once 'helpers/format_helpers.php';

if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Nieprawidłowy token CSRF.");
}

$translated = $_POST['translated'] ?? [];
$original = $_POST['original'] ?? [];
$file = $_POST['file'] ?? [];
$index = $_POST['index'] ?? [];
$lang = $_POST['lang'] ?? $config['default_lang'];

// ✅ DODAJ TUTAJ WALIDACJĘ:
$count_translated = count($translated);
$count_original = count($original);
$count_file = count($file);
$count_index = count($index);

if ($count_translated !== $count_original || 
    $count_translated !== $count_file || 
    $count_translated !== $count_index) {
    die("Błąd: Niespójne dane wejściowe. " .
        "translated: $count_translated, " .
        "original: $count_original, " .
        "file: $count_file, " .
        "index: $count_index");
}

// Dodatkowe zabezpieczenie - sprawdź czy nie ma pustych wartości w kluczowych polach
foreach ($translated as $i => $value) {
    if (!isset($original[$i]) || !isset($file[$i]) || !isset($index[$i])) {
        die("Błąd: Brakujące dane dla indeksu $i");
    }
}

$csvFile = $config['csv_dir'] . 'translated.csv';
$tempPath = $config['temp_dir'];
$outputPath = $config['output_dir'] . 'translated_' . date('Ymd_His') . '.idml';


// Zapis CSV
$fp = fopen($csvFile, 'w');
fputcsv($fp, ['FileName', 'ContentIndex', 'OriginalText', 'TranslatedText']);
foreach ($translated as $i => $value) {
    fputcsv($fp, [$file[$i], $index[$i], $original[$i], $value]);
}
fclose($fp);

// Grupowanie treści po plikach
$grouped = [];
foreach ($translated as $i => $value) {
    $grouped[$file[$i]][] = [
        'index' => $index[$i],
        'text' => restoreSpacesAfterTranslation($value) // 🔹 przywróć spacje
    ];
}

// Zmiana treści XML
foreach ($grouped as $filename => $entries) {
    $xmlPath = $tempPath . 'Stories/' . $filename;
    if (!file_exists($xmlPath)) continue;

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($xmlPath);

    $contents = $dom->getElementsByTagName('Content');
    foreach ($entries as $e) {
        $idx = (int)$e['index'];
        $node = $contents->item($idx);
        if ($node) {
            $node->nodeValue = htmlspecialchars($e['text'], ENT_XML1, 'UTF-8');
        }
    }

    // 💡 opcjonalnie: popraw spacing między Content
    $xmlString = $dom->saveXML();
    $xmlString = preg_replace(
        '/<\/Content>\s*<Content>(?=\p{L}|\p{N})/u',
        '</Content> <Content>',
        $xmlString
    );
    file_put_contents($xmlPath, $xmlString);
}

// Ustawienie folderu wyjściowego
$originalDir = rtrim($config['output_dir'], '/\\');
if (!is_dir($originalDir)) mkdir($originalDir, 0777, true);

// Poprawienie nazwy pliku
$originalName = pathinfo($_POST['original_idml'] ?? 'translated', PATHINFO_FILENAME);
$originalName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
$suffix = '_translated';
$outputPath = $originalDir . '/' . $originalName . $suffix . '.idml';


// Pakowanie IDML
$zip = new ZipArchive();
if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Nie udało się utworzyć pliku IDML: $outputPath");
}

$realTempPath = realpath($config['temp_dir']);
$dir = new RecursiveDirectoryIterator($realTempPath, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($dir);

foreach ($files as $file) {
    if ($file->isFile()) {
        $realPath = $file->getRealPath();
        // Usuwamy ścieżkę bazową i ewentualny ukośnik na początku
        $relative = substr($realPath, strlen($realTempPath));
        $relative = ltrim($relative, DIRECTORY_SEPARATOR);
        // WYMUSZENIE forward slashy dla plików wewnątrz ZIP (ważne dla InDesign)
        $zipPath = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
        $zip->addFile($realPath, $zipPath);
    }
}
$zip->close();


//print_r($_POST);
//exit;


// Pobranie pliku
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename=' . basename($outputPath));
readfile($outputPath);
exit;
