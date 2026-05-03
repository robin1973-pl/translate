require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['reset'];
$db = new SQLite3(__DIR__ . '/users.db');

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

// Verify token
$stmt = $db->prepare("SELECT id FROM users WHERE reset_token = :t AND reset_expires > datetime('now')");
$stmt->bindValue(':t', $token);
$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    die($ui['error_link']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($pass !== $confirm) {
        $error = $ui['error_mismatch'];
    } elseif (strlen($pass) < 6) {
        $error = $ui['error_short'];
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :p, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $stmt->bindValue(':p', $hash);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();
        $success = $ui['success'];
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
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
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
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">INDD TRANSLATION</div>
        <h2 style="font-size: 1.4rem; margin-bottom: 2rem; color: var(--text-main); font-weight: 800;"><?= $ui['title'] ?></h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success"><?= $success ?></div>
            <a href="login.php" class="btn-primary" style="display: block; text-decoration: none;"><?= $ui['login_btn'] ?></a>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><?= $ui['pass_label'] ?></label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $ui['confirm_label'] ?></label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary"><?= $ui['submit'] ?></button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
