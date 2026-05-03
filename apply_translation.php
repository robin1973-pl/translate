<?php include 'auth.php';
// apply_translation.php – Professional version with isolated workspace
require_once 'helpers/text_utils.php';
require_once 'helpers/workspace.php';
$config = require 'config.php';

require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['translate'];

if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die($ui['error_csrf']);
}

$filePath = $_POST['file_path'] ?? '';
if (!file_exists($filePath)) {
    die($ui['error_source_not_found']);
}

$translated = $_POST['translated'] ?? [];
$original = $_POST['original'] ?? [];
$fileKeys = $_POST['file'] ?? [];
$indexKeys = $_POST['index'] ?? [];
$original_name = $_POST['original_idml'] ?? basename($filePath);
$job_id = (int)($_POST['job_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

// Workspace-based rebuild directory
if ($job_id > 0) {
    $tempDir = get_workspace_path($user_id, $job_id) . 'rebuild_idml/';
    $outputDir = get_output_dir($user_id, $job_id);
} else {
    // Legacy fallback
    $tempDir = $config['temp_dir'] . 'rebuild_idml_' . uniqid() . '/';
    $outputDir = $config['output_dir'];
}

if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

// 1. Rozpakuj oryginał
$zip = new ZipArchive;
if ($zip->open($filePath) === TRUE) {
    $zip->extractTo($tempDir);
    $zip->close();
} else {
    die($ui['error_zip_open']);
}

// 2. Grupowanie treści
$grouped = [];
foreach ($translated as $i => $value) {
    if ($value === '') continue; // Skip empty
    $grouped[$fileKeys[$i]][] = [
        'index' => $indexKeys[$i],
        'text' => restoreSpacesAfterTranslation($value)
    ];
}

// 3. Podmień treści w XML
foreach ($grouped as $filename => $entries) {
    $xmlPath = $tempDir . 'Stories/' . $filename;
    if (!file_exists($xmlPath)) continue;

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = true;
    $dom->load($xmlPath);

    $contents = $dom->getElementsByTagName('Content');
    foreach ($entries as $e) {
        $idx = (int)$e['index'];
        if (isset($contents[$idx])) {
            $contents[$idx]->nodeValue = htmlspecialchars($e['text'], ENT_XML1);
        }
    }

    $xmlString = $dom->saveXML();
    // Maintain spacing logic
    $xmlString = preg_replace('/<\/Content>\s*<Content>(?=\p{L}|\p{N})/u', '</Content> <Content>', $xmlString);
    file_put_contents($xmlPath, $xmlString);
}

// 4. Zapakuj z powrotem — unique filename using uniqid()
$outputFile = $outputDir . 'translated_' . uniqid() . '_' . $original_name;
$zip = new ZipArchive;
if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $pathOnDisk = $file->getRealPath();
            $relativePath = substr($pathOnDisk, strlen(realpath($tempDir)) + 1);
            $zip->addFile($pathOnDisk, $relativePath);
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
header('Content-Disposition: attachment; filename="translated_' . $original_name . '"');
header('Content-Length: ' . filesize($outputFile));
readfile($outputFile);

exit;
