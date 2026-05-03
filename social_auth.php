<?php
// social_auth.php – PRO Social Auth Handler
session_start();
$config = require 'config.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';

if (!$provider || !isset($config['social'][$provider])) {
    die("Nieprawidłowy dostawca.");
}

$social = $config['social'][$provider];

// 1. BRAK KODU – PRZEKIERUJ DO DOSTAWCY
if (!$code) {
    if ($provider === 'google') {
        $url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id'     => $social['client_id'],
            'redirect_uri'  => $social['redirect_uri'],
            'response_type' => 'code',
            'scope'         => 'email profile',
            'access_type'   => 'online'
        ]);
    }
    header("Location: " . $url);
    exit;
}

// 2. KOD ISTNIEJE – WYMIEŃ NA TOKEN I POBIERZ DANE
try {
    $userData = [];
    if ($provider === 'google') {
        // Wymiana na Token
        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code'          => $code,
            'client_id'     => $social['client_id'],
            'client_secret' => $social['client_secret'],
            'redirect_uri'  => $social['redirect_uri'],
            'grant_type'    => 'authorization_code'
        ]));
        $tokenRes = json_decode(curl_exec($ch), true);
        
        // Pobierz dane użytkownika
        $ch = curl_init("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $tokenRes['access_token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userData = json_decode(curl_exec($ch), true);
        
        $socialId = $userData['id'];
        $email = $userData['email'];
        $name = $userData['name'];
        $avatar = $userData['picture'];
        $idColumn = 'google_id';
    }

    // 3. LOGOWANIE LUB REJESTRACJA W BAZIE
    $db = new SQLite3(__DIR__ . '/users.db');
    
    // Sprawdź czy użytkownik już istnieje (po social ID lub email)
    $stmt = $db->prepare("SELECT * FROM users WHERE $idColumn = :sid OR email = :email LIMIT 1");
    $stmt->bindValue(':sid', $socialId);
    $stmt->bindValue(':email', $email);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        // Aktualizuj ID społecznościowe jeśli brak
        if (!$user[$idColumn]) {
            $update = $db->prepare("UPDATE users SET $idColumn = :sid, avatar = :avatar WHERE id = :uid");
            $update->bindValue(':sid', $socialId);
            $update->bindValue(':avatar', $avatar);
            $update->bindValue(':uid', $user['id']);
            $update->execute();
        }
    } else {
        // Nowy użytkownik – Rejestracja
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, credits, $idColumn, avatar) 
                             VALUES (:name, :email, :pass, 'user', 5, :sid, :avatar)");
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':pass', password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)); // Losowe hasło
        $stmt->bindValue(':sid', $socialId);
        $stmt->bindValue(':avatar', $avatar);
        $stmt->execute();
        
        $user = ['id' => $db->lastInsertRowID(), 'username' => $name, 'role' => 'user'];
    }

    // 4. SESJA I REDIRECT
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['avatar'] = $avatar;
    
    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    header("Location: login.php?error=social_failed");
    exit;
}
