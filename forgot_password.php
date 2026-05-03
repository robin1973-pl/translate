<?php
session_start();
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['forgot'];
$db = new SQLite3(__DIR__ . '/users.db');

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Add columns if not exists (fail-safe)
        @$db->exec("ALTER TABLE users ADD COLUMN reset_token TEXT");
        @$db->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME");
        
        $stmt = $db->prepare("UPDATE users SET reset_token = :t, reset_expires = :e WHERE id = :id");
        $stmt->bindValue(':t', $token);
        $stmt->bindValue(':e', $expires);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();

        $message = $ui['success'];
        $debug_link = "reset_password.php?token=$token";
    } else {
        $message = $ui['success']; // Consistent feedback for security
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ui['title'] ?> - INDD Translation</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
            background: var(--bg-body);
        }
        .login-card { 
            width: 100%; 
            max-width: 400px; 
            padding: 3rem; 
            border-radius: 24px; 
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            text-align: center;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 2rem;
            letter-spacing: -1px;
            color: var(--accent);
        }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-label { 
            display: block; 
            font-size: 0.85rem; 
            font-weight: 700; 
            color: var(--text-dim); 
            margin-bottom: 0.6rem;
        }
        .form-control {
            width: 100%;
            background: var(--bg-body);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            border-radius: 12px;
            padding: 1rem;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .btn-primary { 
            width: 100%;
            padding: 1.2rem;
            background: var(--accent);
            border: none;
            border-radius: 12px;
            color: var(--btn-text);
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 14px 0 rgba(8, 145, 178, 0.3);
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #10b981;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .back-link {
            display: block;
            margin-top: 1.5rem;
            color: var(--text-dim);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .back-link:hover { color: var(--accent); }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 1.5rem;">
            <a href="?lang=pl" title="Polski" style="text-decoration: none; font-size: 1.2rem;">🇵🇱</a>
            <a href="?lang=en" title="English" style="text-decoration: none; font-size: 1.2rem;">🇬🇧</a>
            <a href="?lang=de" title="Deutsch" style="text-decoration: none; font-size: 1.2rem;">🇩🇪</a>
            <a href="?lang=cs" title="Čeština" style="text-decoration: none; font-size: 1.2rem;">🇨🇿</a>
        </div>
        <div class="logo">INDD TRANSLATION</div>
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem; color: var(--text-main); font-weight: 800;"><?= $ui['title'] ?></h2>
        <p style="color: var(--text-dim); font-size: 0.95rem; margin-bottom: 2rem;"><?= $ui['desc'] ?></p>

        <?php if ($message): ?>
            <div class="alert-success">
                <?= $message ?>
                <?php if (isset($debug_link)): ?>
                    <br><br>
                    <a href="<?= $debug_link ?>" style="color: var(--accent); font-size: 0.8rem;"><?= $ui['debug_click'] ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label"><?= $ui['email_label'] ?></label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
            </div>
            <button type="submit" class="btn-primary"><?= $ui['submit'] ?></button>
        </form>

        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= $ui['back_login'] ?></a>
    </div>
</body>
</html>
