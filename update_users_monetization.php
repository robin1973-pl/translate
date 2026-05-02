<?php
$dbPath = __DIR__ . '/users.db';
$db = new SQLite3($dbPath);

// Add plan and payment columns if they don't exist
$cols = $db->query("PRAGMA table_info(users)");
$hasPlan = false;
while ($row = $cols->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name'] === 'plan') $hasPlan = true;
}

if (!$hasPlan) {
    echo "Adding monetization columns...\n";
    $db->exec("ALTER TABLE users ADD COLUMN plan TEXT DEFAULT 'starter'");
    $db->exec("ALTER TABLE users ADD COLUMN subscription_expires DATE");
    echo "Done.\n";
} else {
    echo "Columns already exist.\n";
}
