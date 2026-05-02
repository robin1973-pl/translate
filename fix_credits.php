<?php
$db = new SQLite3(__DIR__ . '/users.db');
$db->exec("UPDATE users SET credits = 10 WHERE username = 'robin'");
echo "Updated robin to 10 credits\n";
