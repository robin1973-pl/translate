<?php
include 'auth.php';
$config = require 'config.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['orderID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$orderID = $data['orderID'];
$credits_to_add = (int)$data['credits'];

// 1. Pobierz Access Token z PayPal
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ($config['paypal_mode'] === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com') . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $config['paypal_client_id'] . ":" . $config['paypal_secret']);

$result = curl_exec($ch);
$auth = json_decode($result, true);
$accessToken = $auth['access_token'] ?? null;
curl_close($ch);

if (!$accessToken) {
    echo json_encode(['status' => 'error', 'message' => 'Auth failed']);
    exit;
}

// 2. Zweryfikuj zamówienie (Status Capture)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ($config['paypal_mode'] === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com') . "/v2/checkout/orders/$orderID");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$result = curl_exec($ch);
$order = json_decode($result, true);
curl_close($ch);

    if ($order['status'] === 'COMPLETED') {
        // 3. Dodaj kredyty w bazie
        $db = new SQLite3(__DIR__ . '/users.db');
        $stmt = $db->prepare("UPDATE users SET credits = credits + :c WHERE id = :uid");
        $stmt->bindValue(':c', $credits_to_add, SQLITE3_INTEGER);
        $stmt->bindValue(':uid', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->execute();

        // 4. LOGOWANIE TRANSAKCJI
        $stmt_log = $db->prepare("INSERT INTO credit_logs (user_id, amount, reason) VALUES (:uid, :amt, :reason)");
        $stmt_log->bindValue(':uid', $_SESSION['user_id']);
        $stmt_log->bindValue(':amt', $credits_to_add);
        $stmt_log->bindValue(':reason', "Zakup PayPal: " . ($data['amount'] ?? '??') . " PLN (Order: $orderID)");
        $stmt_log->execute();

        echo json_encode(['status' => 'success']);
    } else {
    echo json_encode(['status' => 'error', 'message' => 'Order not completed']);
}
