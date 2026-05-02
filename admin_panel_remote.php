<?php
include 'auth.php';
$db = new SQLite3(__DIR__ . '/users.db');

// Check if user is admin
$stmt = $db->prepare("SELECT role FROM users WHERE id = :uid");
$stmt->bindValue(':uid', $_SESSION['user_id']);
$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Handle actions
if (isset($_POST['action'])) {
    $target_uid = (int)$_POST['user_id'];
    
    if ($_POST['action'] === 'update_credits') {
        $new_credits = (int)$_POST['credits'];
        $stmt_old = $db->prepare("SELECT credits FROM users WHERE id = :id");
        $stmt_old->bindValue(':id', $target_uid);
        $old_credits = $stmt_old->execute()->fetchArray(SQLITE3_ASSOC)['credits'];
        $diff = $new_credits - $old_credits;

        $stmt = $db->prepare("UPDATE users SET credits = :c WHERE id = :id");
        $stmt->bindValue(':c', $new_credits, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $target_uid, SQLITE3_INTEGER);
        $stmt->execute();

        $stmt_log = $db->prepare("INSERT INTO credit_logs (user_id, amount, reason) VALUES (:uid, :amt, 'Zmiana ręczna przez Admina')");
        $stmt_log->bindValue(':uid', $target_uid);
        $stmt_log->bindValue(':amt', $diff);
        $stmt_log->execute();

    } elseif ($_POST['action'] === 'update_plan') {
        $stmt = $db->prepare("UPDATE users SET plan = :p WHERE id = :id");
        $stmt->bindValue(':p', $_POST['plan'], SQLITE3_TEXT);
        $stmt->bindValue(':id', $target_uid, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($_POST['action'] === 'change_password') {
        $new_pass = $_POST['new_password'];
        if (!empty($new_pass)) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
            $stmt->bindValue(':p', $hash);
            $stmt->bindValue(':id', $target_uid);
            $stmt->execute();
        }
    }
    header("Location: admin_panel.php?success=1");
    exit;
}

// Fetch all users
$users = [];
$res = $db->query("SELECT id, username, email, plan, role, credits FROM users");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $stmt_jobs = $db->prepare("SELECT COUNT(*) as count FROM jobs WHERE user_id = :uid");
    $stmt_jobs->bindValue(':uid', $row['id']);
    $row['total_jobs'] = $stmt_jobs->execute()->fetchArray(SQLITE3_ASSOC)['count'];
    $users[] = $row;
}

// Fetch global credit logs
$logs = [];
$log_res = $db->query("SELECT cl.*, u.username FROM credit_logs cl JOIN users u ON cl.user_id = u.id ORDER BY cl.created_at DESC LIMIT 50");
while($l = $log_res->fetchArray(SQLITE3_ASSOC)) { $logs[] = $l; }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora - Translate.pro</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: var(--glass); border-radius: 15px; overflow: hidden; border: 1px solid var(--glass-border); box-shadow: var(--shadow); }
        .admin-table th, .admin-table td { padding: 1.2rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th { background: rgba(255,255,255,0.05); color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .action-form { display: flex; gap: 0.5rem; align-items: center; }
        .input-small { background: var(--bg-body); border: 1px solid var(--glass-border); color: var(--text-main); padding: 0.6rem; border-radius: 8px; width: 70px; font-weight: 700; }
        .input-text { background: var(--bg-body); border: 1px solid var(--glass-border); color: var(--text-main); padding: 0.6rem; border-radius: 8px; font-size: 0.85rem; width: 140px; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .badge-admin { background: #ef4444; color: white; }
        .badge-user { background: var(--accent); color: white; }
        .btn-action { background: var(--accent); color: white; border: none; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: all 0.3s; }
        .btn-action:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .log-container { margin-top: 3rem; background: var(--glass); padding: 2rem; border-radius: 20px; border: 1px solid var(--glass-border); }
        .log-item { display: flex; justify-content: space-between; padding: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        .amount-pos { color: #10b981; font-weight: 700; }
        .amount-neg { color: #ef4444; font-weight: 700; }
    </style>
</head>
<body class="dashboard-body">
    <header class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: var(--bg-nav); backdrop-filter: blur(10px); border-bottom: 1px solid var(--glass-border); position: sticky; top: 0; z-index: 100;">
        <a href="dashboard.php" class="logo" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
            Translate.pro 
            <span style="background: var(--accent); font-size: 0.75rem; padding: 4px 10px; border-radius: 8px; color: #ffffff !important; font-weight: 900; letter-spacing: 0.1em; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">ADMIN</span>
        </a>
        <nav style="display: flex; align-items: center; gap: 1.5rem;">
            <a href="dashboard.php" style="background: var(--glass); border: 1px solid var(--glass-border); padding: 0.6rem 1.2rem; border-radius: 12px; font-weight: 700; text-decoration: none; color: var(--text-main); font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-chevron-left" style="font-size: 0.7rem;"></i> Powrót do Panelu
            </a>
            <a href="logout.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.85rem; font-weight: 600;">Wyloguj</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="title-hero">
            <h1 style="color: var(--accent);">Zarządzanie Królestwem</h1>
            <p>Pełna kontrola nad użytkownikami, finansami i historią operacji.</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; text-align: center; font-weight: 700;">
                <i class="fas fa-check-circle"></i> Zmiany zostały zapisane pomyślnie.
            </div>
        <?php endif; ?>

        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;"><i class="fas fa-users"></i> Lista Użytkowników</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Użytkownik</th>
                    <th>Plan</th>
                    <th>Stan Kredytów</th>
                    <th>Hasło</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800;"><?= htmlspecialchars($u['username']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-dim);"><?= htmlspecialchars($u['email']) ?></div>
                        <span class="badge badge-<?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span>
                        <div style="font-size: 0.7rem; margin-top: 5px;"><i class="fas fa-file-invoice"></i> <?= $u['total_jobs'] ?> projektów</div>
                    </td>
                    <td>
                        <form action="" method="POST" class="action-form">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="update_plan">
                            <select name="plan" onchange="this.form.submit()">
                                <option value="starter" <?= $u['plan'] === 'starter' ? 'selected' : '' ?>>Starter</option>
                                <option value="professional" <?= $u['plan'] === 'professional' ? 'selected' : '' ?>>Pro</option>
                                <option value="agency" <?= $u['plan'] === 'agency' ? 'selected' : '' ?>>Agency</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form action="" method="POST" class="action-form">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="update_credits">
                            <input type="number" name="credits" value="<?= $u['credits'] ?>" class="input-small">
                            <button type="submit" class="btn-action">Zapisz</button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="POST" class="action-form">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="change_password">
                            <input type="text" name="new_password" placeholder="Ustaw nowe" class="input-text">
                            <button type="submit" class="btn-action" style="background: #4b5563;">Resetuj</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="log-container">
            <h2 style="margin-bottom: 1.5rem; color: var(--text-main);"><i class="fas fa-history"></i> Ostatnie operacje i płatności</h2>
            <?php foreach($logs as $l): ?>
                <div class="log-item">
                    <span>
                        <b style="color: var(--accent);"><?= htmlspecialchars($l['username']) ?></b> 
                        <span style="color: var(--text-dim); font-size: 0.8rem; margin-left: 10px;"><?= $l['created_at'] ?></span>
                        <div style="font-size: 0.85rem; margin-top: 2px;"><?= htmlspecialchars($l['reason']) ?></div>
                    </span>
                    <span class="<?= $l['amount'] > 0 ? 'amount-pos' : 'amount-neg' ?>">
                        <?= $l['amount'] > 0 ? '+' : '' ?><?= $l['amount'] ?> kredytów
                    </span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <p style="text-align: center; color: var(--text-dim);">Brak zarejestrowanych operacji.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
