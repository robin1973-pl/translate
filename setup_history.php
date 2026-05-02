<?php
$db = new SQLite3(__DIR__ . '/users.db');

$query = "CREATE TABLE IF NOT EXISTS jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    filename TEXT,
    file_type TEXT,
    target_lang TEXT,
    status TEXT DEFAULT 'uploaded',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($db->exec($query)) {
    echo "Tabela 'jobs' została utworzona pomyślnie.\n";
} else {
    echo "Błąd podczas tworzenia tabeli.\n";
}
