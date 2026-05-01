<?php // setup_db.php
$dbFile = 'users.db';
$db = new SQLite3($dbFile);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    role TEXT DEFAULT 'user'
)");

// Dodaj domyślnego użytkownika admin (jeśli nie istnieje)
$username = 'admin';
$password = 'admin123'; // Docelowo zmień to hasło!
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, role) VALUES (:u, :p, 'admin')");
$stmt->bindValue(':u', $username);
$stmt->bindValue(':p', $hash);
$stmt->execute();

echo "Baza danych gotowa. Użytkownik 'admin' został stworzony (hasło: admin123).\n";
?>
