<?php
$dbPath = __DIR__ . '/users.db';
$db = new SQLite3($dbPath);

// Add credits column
$cols = $db->query("PRAGMA table_info(users)");
$hasCredits = false;
while ($row = $cols->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name'] === 'credits') $hasCredits = true;
}

if (!$hasCredits) {
    echo "Adding credits column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN credits INTEGER DEFAULT 0");
    // Give existing users 3 initial credits
    $db->exec("UPDATE users SET credits = 3 WHERE plan = 'starter'");
    echo "Done.\n";
} else {
    echo "Credits column already exists.\n";
}
