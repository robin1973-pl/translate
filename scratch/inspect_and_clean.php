<?php
// scratch/inspect_and_clean.php — one-time cleanup script
$db = new SQLite3(__DIR__ . '/../users.db');

echo "=== JOBS IN DATABASE ===\n";
$r = $db->query("SELECT id, user_id, filename, status, file_type, target_lang, output_path, created_at FROM jobs ORDER BY id");
$count = 0;
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
    $count++;
}
echo "Total jobs: $count\n\n";

echo "=== CREDIT LOGS ===\n";
$r2 = $db->query("SELECT COUNT(*) as cnt FROM credit_logs");
$row2 = $r2->fetchArray(SQLITE3_ASSOC);
echo "Total credit logs: " . $row2['cnt'] . "\n\n";

echo "=== USERS ===\n";
$r3 = $db->query("SELECT id, username, email, role, credits FROM users");
while ($u = $r3->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($u) . "\n";
}
