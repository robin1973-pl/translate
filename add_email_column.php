<?php
$db = new SQLite3(__DIR__ . '/users.db');

// Add email column if not exists
$cols = $db->query("PRAGMA table_info(users)");
$hasEmail = false;
while ($row = $cols->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name'] === 'email') $hasEmail = true;
}

if (!$hasEmail) {
    echo "Adding email column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN email TEXT");
    // Set default email for existing users
    $db->exec("UPDATE users SET email = 'admin@example.com' WHERE username = 'admin'");
    $db->exec("UPDATE users SET email = 'robin@example.com' WHERE username = 'robin'");
    echo "Done.\n";
} else {
    echo "Email column already exists.\n";
}
