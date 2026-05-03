<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$config = require '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$text = $data['text'] ?? '';
$targetLang = $data['targetLang'] ?? '';

if (!$text || !$targetLang) {
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// 1. Initialize Cache DB
$dbFile = '../cache_translations.db';
$db = new SQLite3($dbFile);
$db->exec("CREATE TABLE IF NOT EXISTS cache (
    hash TEXT PRIMARY KEY,
    original TEXT,
    translated TEXT,
    lang TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 2. Check Cache
$hash = md5($text . '|' . $targetLang);
$stmt = $db->prepare("SELECT translated FROM cache WHERE hash = :h");
$stmt->bindValue(':h', $hash);
$result = $stmt->execute();
$cached = $result->fetchArray(SQLITE3_ASSOC);

if ($cached) {
    echo json_encode([
        'translated' => $cached['translated'],
        'source' => 'cache'
    ]);
    exit;
}

// 3. Call OpenAI if not in cache
try {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    $payload = json_encode([
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => "You are a professional translator. Translate accurately into $targetLang. Preserve all technical symbols like °, ², ³, ₀, ₁, ₂, ₊, ⁻. Do not translate DMX channel names (CH 1, SL 1)."
            ],
            ['role' => 'user', 'content' => "Translate this into $targetLang:\n\n$text"]
        ],
        'temperature' => 0.3
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['openai_key']
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        echo json_encode(['error' => 'OpenAI API error', 'status' => $status, 'details' => $response]);
        exit;
    }

    $resData = json_decode($response, true);
    $translatedText = $resData['choices'][0]['message']['content'] ?? '';

    if ($translatedText) {
        // 4. Save to Cache
        $stmt = $db->prepare("INSERT INTO cache (hash, original, translated, lang) VALUES (:h, :o, :t, :l)");
        $stmt->bindValue(':h', $hash);
        $stmt->bindValue(':o', $text);
        $stmt->bindValue(':t', $translatedText);
        $stmt->bindValue(':l', $targetLang);
        $stmt->execute();

        echo json_encode([
            'translated' => $translatedText,
            'source' => 'api'
        ]);
    } else {
        echo json_encode(['error' => 'No translation returned']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
