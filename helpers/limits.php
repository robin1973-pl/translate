<?php

function check_user_limits($userId, $db) {
    // 1. Get user plan and credits
    $stmt = $db->prepare("SELECT plan, role, credits FROM users WHERE id = :id");
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user['role'] === 'admin') return [
        'allowed' => true,
        'credits' => '∞',
        'plan' => 'Admin'
    ];

    $plan = $user['plan'] ?: 'starter';
    $credits = (int)($user['credits'] ?? 0);

    if ($credits <= 0) {
        return [
            'allowed' => false,
            'credits' => 0,
            'plan' => $plan
        ];
    }

    return [
        'allowed' => true,
        'credits' => $credits,
        'plan' => $plan
    ];
}

function deduct_credit($userId, $db) {
    $stmt = $db->prepare("UPDATE users SET credits = credits - 1 WHERE id = :id AND credits > 0");
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    
    if ($res) {
        $stmtLog = $db->prepare("INSERT INTO credit_logs (user_id, amount, reason) VALUES (:uid, -1, 'Tłumaczenie projektu')");
        $stmtLog->bindValue(':uid', $userId, SQLITE3_INTEGER);
        $stmtLog->execute();
    }
    return $res;
}
