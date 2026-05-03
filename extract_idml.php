<?php include 'auth.php'; // extract_idml.php

// ✅ DODAJ DEBUGOWANIE NA POCZĄTKU
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once __DIR__ . '/helpers/xml_cleaner.php';
$config = require 'config.php';

// ✅ SPRAWDŹ CZY PLIK ISTNIEJE
if (!file_exists(__DIR__ . '/helpers/xml_cleaner.php')) {
    die("Błąd: Nie znaleziono helpers/xml_cleaner.php");
}



// 🔧 Funkcja usuwająca cały katalog (rekurencyjnie)
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
}

// ---------------------------
// Funkcje ochrony spacji
// ---------------------------

// 🔹 Zabezpiecz spacje przed wysłaniem do tłumacza
function protectSpacesForTranslation(string $text): string {
    // Leading spaces
    $text = preg_replace_callback('/^(\s+)/u', function($m){
        return '{__LEADSPACES__' . strlen($m[1]) . '__}';
    }, $text);

    // Trailing spaces
    $text = preg_replace_callback('/(\s+)$/u', function($m){
        return '{__TRAILSPACES__' . strlen($m[1]) . '__}';
    }, $text);

    return $text;
}

// 🔹 Przywróć spacje po tłumaczeniu (używane też w apply_translation.php)
function restoreSpacesAfterTranslation(string $text): string {
    $text = preg_replace_callback('/\{__LEADSPACES__(\d+)__\}/u', function($m){
        return str_repeat(' ', (int)$m[1]);
    }, $text);

    $text = preg_replace_callback('/\{__TRAILSPACES__(\d+)__\}/u', function($m){
        return str_repeat(' ', (int)$m[1]);
    }, $text);

    return $text;
}

// ---------------------------
// Główna logika
// ---------------------------

require_once 'helpers/limits.php';

$dbPath = __DIR__ . '/users.db';
$db = new SQLite3($dbPath);

$limitCheck = check_user_limits($_SESSION['user_id'], $db);
if (!$limitCheck['allowed']) {
    header("Location: dashboard.php?error=limit_reached");
    exit;
}

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

    // Upewnij się, że folder uploads istnieje
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $zipPath = $uploadDir . time() . '_' . $basename . '.idml';
    move_uploaded_file($tmpPath, $zipPath);

    // 📝 ZAPISZ W HISTORII (JOBS)
    if (isset($_SESSION['user_id'])) {
        $db = new SQLite3(__DIR__ . '/users.db');
        $stmt = $db->prepare("INSERT INTO jobs (user_id, filename, file_type, target_lang, status) VALUES (:uid, :fname, 'idml', :lang, 'uploaded')");
        $stmt->bindValue(':uid', $_SESSION['user_id']);
        $stmt->bindValue(':fname', $uploadName);
        $stmt->bindValue(':lang', $lang);
        $stmt->execute();
        
        // DEDUCT CREDIT
        deduct_credit($_SESSION['user_id'], $db);
    }

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
    $job_id = $db->lastInsertRowID();
    header("Location: translate_ui.php?lang=$lang&job_id=$job_id&original_idml=" . urlencode($uploadName));
    exit;

}

echo "Nieprawidłowe wywołanie.";