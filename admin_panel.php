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
    if ($_POST['action'] === 'add_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, plan, credits, role) VALUES (:u, :e, :p, 'starter', 10, 'user')");
        $stmt->bindValue(':u', $username);
        $stmt->bindValue(':e', $email);
        $stmt->bindValue(':p', $password);
        $stmt->execute();
        header("Location: admin_panel.php?success=user_added");
        exit;
    }
    
    $target_uid = (int)$_POST['user_id'];
    
    if ($_POST['action'] === 'update_credits') {
        $new_credits = (int)$_POST['credits'];
        $stmt = $db->prepare("UPDATE users SET credits = :c WHERE id = :id");
        $stmt->bindValue(':c', $new_credits, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $target_uid, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_user') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
        $stmt->bindValue(':id', $target_uid);
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
$log_res = $db->query("SELECT cl.*, u.username FROM credit_logs cl JOIN users u ON cl.user_id = u.id ORDER BY cl.created_at DESC LIMIT 10");
while($l = $log_res->fetchArray(SQLITE3_ASSOC)) { $logs[] = $l; }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Premium Minimalist</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --text-muted: #64748b;
        }
        body.dark-mode {
            --card-bg: #0f172a;
            --card-border: #1e293b;
        }
        .container-min { max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem; }
        
        .header-royal { display: flex; justify-content: space-between; align-items: center; padding-bottom: 2rem; margin-bottom: 2rem; border-bottom: 1px solid var(--card-border); }
        
        .section-header { display: flex; align-items: center; justify-content: space-between; margin: 2rem 0 1rem; }
        .section-title { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.1rem; color: var(--text-main); }
        .section-icon { color: var(--accent); }

        /* USER CARD */
        .min-card { 
            background: var(--card-bg); 
            border: 1px solid var(--card-border); 
            border-radius: 16px; 
            margin-bottom: 0.8rem; 
            padding: 1.2rem 1.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, border-color 0.2s;
        }
        .min-card:hover { transform: translateY(-2px); border-color: var(--accent); }

        .user-info { flex: 1; }
        .user-name { font-weight: 700; font-size: 1rem; margin-bottom: 2px; }
        .user-email { font-size: 0.85rem; color: var(--text-muted); }

        .user-meta { display: flex; align-items: center; gap: 2rem; flex: 1; justify-content: center; }
        .meta-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }

        /* ACTIONS */
        .actions { display: flex; align-items: center; gap: 8px; }
        .action-btn { 
            background: transparent; 
            border: none; 
            color: var(--text-muted); 
            padding: 10px; 
            border-radius: 10px; 
            cursor: pointer; 
            transition: all 0.2s; 
            font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
        }
        .action-btn:hover { background: rgba(212, 175, 55, 0.1); color: var(--accent); }
        .action-btn.delete:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        /* ADD BUTTON */
        .add-btn {
            width: 100%;
            padding: 1.2rem;
            background: transparent;
            border: 2px dashed var(--card-border);
            border-radius: 16px;
            color: var(--text-muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            margin: 2rem 0;
            transition: all 0.3s;
        }
        .add-btn:hover { border-color: var(--accent); color: var(--accent); background: rgba(212, 175, 55, 0.03); }

        /* MODAL */
        #addUserModal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 1000;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: var(--card-bg); padding: 3rem; border-radius: 24px; width: 100%; max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 1px solid var(--card-border);
        }

        .input-min { border: 1px solid var(--card-border); background: var(--bg-body); border-radius: 8px; padding: 6px 12px; font-size: 0.9rem; width: 70px; color: var(--text-main); font-weight: 700; text-align: center; }
        
        /* LOGS GRID */
        .logs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
        .log-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body class="dashboard-body">
    <div class="container-min">
        <header class="header-royal">
            <a href="dashboard.php" class="logo" style="text-decoration: none; font-size: 1.5rem;">
                Translate.pro 
                <span style="background: var(--accent); font-size: 0.75rem; padding: 4px 12px; border-radius: 8px; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 900; letter-spacing: 0.1em; margin-left: 10px; box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3);">ADMIN</span>
            </a>
            <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none; font-weight: 700; font-size: 0.9rem;"><i class="fas fa-times"></i> Zamknij</a>
        </header>

        <div class="section-header">
            <div class="section-title"><i class="fas fa-folder section-icon"></i> Użytkownicy systemu</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;"><?= count($users) ?> kont aktywnych</div>
        </div>

        <?php foreach($users as $u): ?>
        <div class="min-card">
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($u['username']) ?></div>
                <div class="user-email"><?= htmlspecialchars($u['email']) ?></div>
            </div>

            <div class="user-meta">
                <div class="meta-item">
                    <form action="" method="POST" style="display:flex; align-items:center; gap:8px;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="update_credits">
                        <i class="fas fa-coins" style="color: #d4af37;"></i>
                        <input type="number" name="credits" value="<?= $u['credits'] ?>" class="input-min" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="meta-item"><i class="fas fa-layer-group"></i> <?= strtoupper($u['plan']) ?></div>
            </div>

            <div class="actions">
                <form action="" method="POST" style="display:flex;">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="action" value="change_password">
                    <input type="text" name="new_password" placeholder="Hasło" id="pw-<?= $u['id'] ?>" style="display:none; width: 100px; margin-right: 5px;" class="input-min">
                    <button type="button" onclick="let el=document.getElementById('pw-<?= $u['id'] ?>'); if(el.style.display==='none'){el.style.display='inline-block';}else{this.form.submit();}" class="action-btn" title="Resetuj hasło"><i class="fas fa-key"></i></button>
                </form>

                <button class="action-btn" title="Edytuj dane"><i class="fas fa-user-edit"></i></button>

                <form action="" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć użytkownika <?= htmlspecialchars($u['username']) ?>?');" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="action" value="delete_user">
                    <button type="submit" class="action-btn delete" title="Usuń"><i class="fas fa-trash-alt"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <button onclick="document.getElementById('addUserModal').style.display='flex'" class="add-btn">
            <i class="fas fa-plus-circle"></i> Dodaj nowego użytkownika do systemu
        </button>

        <div class="section-header">
            <div class="section-title"><i class="fas fa-history section-icon"></i> Ostatnie operacje</div>
        </div>
        
        <div class="logs-grid">
            <?php foreach($logs as $l): ?>
            <div class="log-card">
                <div class="user-info">
                    <div class="user-name" style="font-size: 0.85rem;"><?= htmlspecialchars($l['username']) ?></div>
                    <div class="user-email" style="font-size: 0.75rem;"><?= htmlspecialchars($l['reason']) ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 800; color: <?= $l['amount'] > 0 ? '#10b981' : '#ef4444' ?>;">
                        <?= $l['amount'] > 0 ? '+' : '' ?><?= $l['amount'] ?>
                    </div>
                    <div style="font-size: 0.65rem; color: var(--text-muted);"><?= $l['created_at'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- MODAL -->
    <div id="addUserModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 2rem; color: var(--accent);">Nowy Użytkownik</h2>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add_user">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-muted);">Nazwa</label>
                    <input type="text" name="username" class="modal-input" required style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--card-border); background: var(--bg-body); color: var(--text-main);">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-muted);">E-mail</label>
                    <input type="email" name="email" class="modal-input" required style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--card-border); background: var(--bg-body); color: var(--text-main);">
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="display:block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-muted);">Hasło</label>
                    <input type="password" name="password" class="modal-input" required style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--card-border); background: var(--bg-body); color: var(--text-main);">
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-action" style="flex: 2; padding: 12px;">Utwórz konto</button>
                    <button type="button" onclick="document.getElementById('addUserModal').style.display='none'" class="btn-action" style="flex: 1; background: #64748b;">Anuluj</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
