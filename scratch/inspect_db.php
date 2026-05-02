<?php
$db = new SQLite3('users.db');
$result = $db->query("SELECT sql FROM sqlite_master WHERE type='table'");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo $row['sql'] . "\n";
}
