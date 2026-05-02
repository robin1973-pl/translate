<?php
include 'auth.php';
$db = new SQLite3(__DIR__ . '/users.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credits'])) {
    $credits = (int)$_POST['credits'];
    $userId = $_SESSION['user_id'];

    $stmt = $db->prepare("UPDATE users SET credits = credits + :c WHERE id = :uid");
    $stmt->bindValue(':c', $credits, SQLITE3_INTEGER);
    $stmt->bindValue(':uid', $userId, SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: dashboard.php?success=payment_complete");
    exit;
}
header("Location: dashboard.php");
