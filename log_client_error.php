<?php
// log_client_error.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Funkcja do logowania (ta sama co w error_log.php)
function log_client_error(string $message, array $context = []): void {
    $log_file = __DIR__ . '/logs/client_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $log_message = "[$timestamp] $message $context_str" . PHP_EOL;
    
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Zapisz błąd
log_client_error('CLIENT_ERROR', $input);

echo json_encode(['status' => 'logged']);
?>