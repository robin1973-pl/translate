<?php
session_start();
$strings = require 'helpers/ui_strings.php';
$ui_lang = 'pl';
$ui = $strings[$ui_lang]['register'];

$message = '';
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = new SQLite3('users.db');
        
        // Sprawdź czy użytkownik istnieje
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :u");
        $stmt->bindValue(':u', $username);
        $result = $stmt->execute();
        
        if ($result->fetchArray()) {
            $message = $ui['error'];
            $is_error = true;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:u, :p)");
            $stmt->bindValue(':u', $username);
            $stmt->bindValue(':p', $hash);
            if ($stmt->execute()) {
                $message = $ui['success'];
            } else {
                $message = "Błąd zapisu.";
                $is_error = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $ui['title'] ?></title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { 
            height: 100vh; display: flex; align-items: center; justify-content: center; 
            margin: 0;
        }
        .register-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            padding: 3rem;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow);
        }
        h2 { margin-bottom: 2rem; text-align: center; color: var(--text-main); }
        input {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1.2rem;
            background: var(--bg-body);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
        }
        button {
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
        button:hover {
            transform: translateY(-2px);
            background: var(--accent-hover);
        }
        .message {
            margin-bottom: 1.5rem;
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
        }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: var(--text-dim); text-decoration: none; font-weight: 500; }
        .back-link:hover { color: var(--accent); }
    </style>
</head>
<body>
    <div class="register-card">
        <h2><?= $ui['title'] ?></h2>
        
        <?php if ($message): ?>
            <div class="message <?= $is_error ? 'error' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit"><?= $ui['submit'] ?></button>
        </form>
        
        <a href="login.php" class="back-link">Masz już konto? Zaloguj się</a>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
