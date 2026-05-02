<?php
$db = new SQLite3(__DIR__ . '/users.db');

// Create credit_logs table
$db->exec("CREATE TABLE IF NOT EXISTS credit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    amount INTEGER,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

echo "Tabela credit_logs gotowa.\n";
