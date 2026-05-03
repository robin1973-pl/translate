<?php
// inspect_db.php
$db = new SQLite3(__DIR__ . '/users.db');

echo "--- USERS ---\n";
$res = $db->query("SELECT id, username, email FROM users");
while($row = $res->fetchArray(SQLITE3_ASSOC)) print_r($row);

echo "\n--- JOBS ---\n";
$res = $db->query("SELECT * FROM jobs ORDER BY id DESC LIMIT 10");
while($row = $res->fetchArray(SQLITE3_ASSOC)) print_r($row);

echo "\n--- PAYMENTS ---\n";
$res = $db->query("SELECT * FROM payments ORDER BY id DESC LIMIT 10");
while($row = $res->fetchArray(SQLITE3_ASSOC)) print_r($row);
?>
