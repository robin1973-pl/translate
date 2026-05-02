<?php
session_start();
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

// Możesz tu dodać wybór języka w przyszłości, teraz domyślnie PL
$ui_lang = 'pl';
$ui = $strings[$ui_lang]['login'];

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (file_exists('users.db')) {
        $db = new SQLite3('users.db');
        $stmt = $db->prepare("SELECT id, password FROM users WHERE username = :u");
        $stmt->bindValue(':u', $username);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = $ui['error'];
        }
    } else {
        $error = "Baza danych nie istnieje. Uruchom setup_db.php";
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ui['title'] ?></title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
            overflow: hidden;
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
        }
        .logo {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 2rem;
            letter-spacing: -1px;
            color: var(--accent);
        }

        .form-group { margin-bottom: 1.5rem; }
        .form-label { 
            display: block; 
            font-size: 0.85rem; 
            font-weight: 700; 
            color: var(--text-dim); 
            margin-bottom: 0.6rem;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%;
            background: var(--bg-body);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            border-radius: 12px;
            padding: 1rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
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
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px 0 rgba(8, 145, 178, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: var(--accent-hover);
        }
        .error-msg {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        .back-home {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-dim);
            text-decoration: none;
            font-weight: 600;
        }
        .back-home:hover { color: var(--accent); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">TRANSLATE.PRO</div>
        <h2 style="font-size: 1.4rem; margin-bottom: 2rem; text-align: center; color: var(--text-main); font-weight: 800;"><?= $ui['welcome_back'] ?></h2>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label"><?= $ui['username'] ?></label>
                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label"><?= $ui['password'] ?></label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary"><?= $ui['submit'] ?></button>
        </form>

        <a href="forgot_password.php" class="back-home" style="margin-top: 2rem; font-size: 0.9rem; opacity: 0.8;">Zapomniałeś hasła?</a>
        <a href="index.php" class="back-home">Wróć do strony głównej</a>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
