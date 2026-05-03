<?php
$db = new SQLite3('users.db');
$db->exec("UPDATE jobs SET status = 'completed' WHERE status = 'uploaded' AND user_id = 3");
echo "Done.";
