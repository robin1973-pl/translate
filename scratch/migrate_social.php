<?php
// migrate_social.php
$db = new SQLite3(__DIR__ . '/users.db');

// Dodajemy kolumny jeśli nie istnieją
@$db->exec("ALTER TABLE users ADD COLUMN google_id TEXT");
@$db->exec("ALTER TABLE users ADD COLUMN facebook_id TEXT");
@$db->exec("ALTER TABLE users ADD COLUMN avatar TEXT");

echo "Baza danych zaktualizowana o pola Social Auth. \n";
?>
