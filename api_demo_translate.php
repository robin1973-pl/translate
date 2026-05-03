<?php
header('Content-Type: application/json');

// Włączanie raportowania błędów do JSONa, aby nie psuć odpowiedzi
error_reporting(0);
ini_set('display_errors', 0);

$config = require 'config.php';
$languages = require 'helpers/languages.php';

$selection = $_POST['v'] ?? 'tadeusz';
$target_lang_code = $_POST['lang'] ?? 'en';
$target_lang_name = $languages[$target_lang_code] ?? 'English';

$options = [
    'tadeusz' => "Litwo! Ojczyzno moja! ty jesteś jak zdrowie:\nIle cię trzeba cenić, ten tylko się dowie,\nKto cię stracił. Dziś piękność twą w całej ozdobie\nWidzę i opisuję, bo tęsknię po tobie.",
    'shakespeare' => "Shall I compare thee to a summer’s day?\nThou art more lovely and more temperate:\nRough winds do shake the darling buds of May,\nAnd summer’s lease hath all too short a date."
];

$text = $options[$selection] ?? $options['tadeusz'];

// CACHE LOGIC: Sprawdzamy czy mamy już to tłumaczenie na dysku
$cacheDir = __DIR__ . '/cache/demo/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

$cacheFile = $cacheDir . md5($selection . $target_lang_code) . '.txt';

if (file_exists($cacheFile)) {
    echo json_encode([
        'status' => 'success',
        'translation' => file_get_contents($cacheFile),
        'cached' => true
    ]);
    exit;
}

// OpenAI API Call
$apiKey = $config['openai_key'];

if (empty($apiKey)) {
    echo json_encode(['status' => 'error', 'message' => 'Brak klucza API OpenAI w konfiguracji.']);
    exit;
}

$ch = curl_init('https://api.openai.com/v1/chat/completions');

$postData = [
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'system', 'content' => "Translate the following text accurately. Maintain the poetic style of the original author. Target language: {$target_lang_name}"],
        ['role' => 'user', 'content' => $text]
    ],
    'temperature' => 0.7
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

// DODANE: Limity czasu, aby zapobiec "wiszeniu"
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10s na połączenie
curl_setopt($ch, CURLOPT_TIMEOUT, 50);        // 50s na całą operację

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Błąd połączenia (cURL): ' . $curlError
    ]);
    exit;
}

if ($httpCode !== 200) {
    $resData = json_decode($response, true);
    $errMsg = $resData['error']['message'] ?? "Błąd API OpenAI (Kod: $httpCode)";
    echo json_encode([
        'status' => 'error',
        'message' => $errMsg
    ]);
    exit;
}

$resData = json_decode($response, true);

if (isset($resData['choices'][0]['message']['content'])) {
    $translation = trim($resData['choices'][0]['message']['content']);
    
    // Zapisujemy do cache na przyszłość
    file_put_contents($cacheFile, $translation);

    echo json_encode([
        'status' => 'success',
        'translation' => $translation
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Otrzymano pustą odpowiedź z API.'
    ]);
}
