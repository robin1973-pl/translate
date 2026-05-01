<?php
session_start();
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

// Możesz tu dodać wybór języka w przyszłości, teraz domyślnie PL
$ui_lang = 'pl';
$ui = $strings[$ui_lang]['login'];

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
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
            header("Location: index.php");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #081425;
            --accent: #06B6D4;
            --accent-glow: rgba(6, 182, 212, 0.3);
            --glass: rgba(15, 23, 42, 0.6);
            --glass-border: rgba(255, 255, 255, 0.1);
        }
        body { 
            background: radial-gradient(circle at top right, #1e293b, #081425);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Inter', sans-serif;
            color: #d8e3fb;
            margin: 0;
            overflow: hidden;
        }
        /* Background decorative glows */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: var(--accent-glow);
            filter: blur(100px);
            border-radius: 50%;
            z-index: -1;
        }
        .glow-1 { top: -100px; right: -100px; }
        .glow-2 { bottom: -100px; left: -100px; }

        .login-card { 
            width: 100%; 
            max-width: 400px; 
            padding: 2.5rem; 
            border-radius: 20px; 
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .logo {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            letter-spacing: -1px;
            color: white;
        }
        .logo span { color: var(--accent); }

        .form-group { margin-bottom: 1.5rem; }
        .form-label { 
            display: block; 
            font-size: 0.75rem; 
            font-weight: 600; 
            color: #869397; 
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 8px;
            padding: 0.75rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 10px var(--accent-glow);
        }
        .btn-primary { 
            width: 100%;
            background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 8px; 
            padding: 0.85rem; 
            font-weight: 600; 
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.6);
        }
        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>
    
    <div class="login-card">
        <div class="logo">IDML<span>Translator</span></div>
        <h2 style="font-size: 1.25rem; margin-bottom: 2rem; text-align: center;"><?= $ui['welcome_back'] ?></h2>
        
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
    </div>
</body>
</html>
