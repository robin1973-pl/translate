<?php
session_start();
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';

// Get language from URL, then Session, then Browser, then default
$ui_lang = $_GET['lang'] ?? get_user_language();
$h = $strings[$ui_lang]['help'] ?? $strings['en']['help'];
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $h['title'] ?> - INDD Translation</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .manual-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .step-card {
            display: flex;
            gap: 24px;
            margin-bottom: 2rem;
            align-items: flex-start;
            padding: 1.5rem;
            background: rgba(var(--accent-rgb, 0, 113, 227), 0.03);
            border-radius: 16px;
        }
        .step-number {
            width: 36px;
            height: 36px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.2);
        }
        .accent-box {
            border-left: 6px solid var(--accent);
            padding: 3rem 3rem 3rem 4rem !important;
            margin-left: 0;
        }
        .security-box {
            background: rgba(255, 59, 48, 0.05) !important;
            border: 1px solid rgba(255, 59, 48, 0.1) !important;
            padding: 3rem !important;
        }
    </style>
</head>
<body class="<?= $_SESSION['theme'] ?? '' ?>">
    <nav class="nav-sidebar">
        <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 12px; padding: 0 10px;">
            <div style="width: 32px; height: 32px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-pen-nib" style="color: white !important;"></i>
            </div>
            <span style="font-weight: 800; font-size: 1.2rem; letter-spacing: -0.5px; color: var(--text-main);">INDD Translation</span>
        </div>
        
        <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php' ?>" class="nav-link">
            <i class="fas fa-house"></i> <span><?= $strings[$ui_lang]['nav']['home'] ?></span>
        </a>
        
        <a href="help.php" class="nav-link active">
            <i class="fas fa-circle-question"></i> <span><?= $strings[$ui_lang]['nav']['help'] ?></span>
        </a>

        <div style="margin-top: auto; border-top: 1px solid var(--border); padding-top: 1.5rem;">
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="nav-link" style="color: #ff3b30;">
                <i class="fas fa-right-from-bracket" style="color: #ff3b30 !important;"></i> <span><?= $strings[$ui_lang]['nav']['logout'] ?></span>
            </a>
            <?php else: ?>
            <a href="login.php" class="nav-link">
                <i class="fas fa-right-to-bracket"></i> <span><?= $strings[$ui_lang]['nav']['login'] ?></span>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="main-content">
        <div class="manual-container">
            <header style="margin-bottom: 4rem;">
                <h1 style="font-size: 3.5rem; font-weight: 800; letter-spacing: -2px; margin-bottom: 1.5rem; line-height: 1.1;"><?= $h['title'] ?></h1>
                <div style="display: flex; gap: 8px;">
                    <?php 
                    $flags = ['pl' => '🇵🇱', 'en' => '🇬🇧', 'de' => '🇩🇪', 'cs' => '🇨🇿'];
                    foreach($flags as $lang => $flag): ?>
                    <a href="?lang=<?= $lang ?>" style="text-decoration: none; padding: 8px 16px; border-radius: 10px; background: <?= $ui_lang == $lang ? 'var(--accent)' : 'var(--bg-card)' ?>; color: <?= $ui_lang == $lang ? 'white' : 'var(--text-main)' ?>; font-size: 1.1rem; font-weight: 700; border: 1px solid var(--border); transition: var(--transition);">
                        <?= $flag ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </header>

            <section class="glass-card" style="margin-bottom: 3rem; padding: 3rem;">
                <h2 style="margin-bottom: 2rem; font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 15px; color: var(--accent);">
                    <i class="fas fa-file-export"></i> <?= $h['how_to_title'] ?>
                </h2>
                <p style="margin-bottom: 3rem; color: var(--text-dim); font-size: 1.1rem;"><?= $h['how_to_desc'] ?></p>
                
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div style="font-size: 1.05rem;"><?= $h['step_1'] ?></div>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div style="font-size: 1.05rem;"><?= $h['step_2'] ?></div>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div style="font-size: 1.05rem;"><?= $h['step_3'] ?></div>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div style="font-size: 1.05rem;"><?= $h['step_4'] ?></div>
                </div>
            </section>

            <section class="glass-card accent-box" style="margin-bottom: 3rem;">
                <h2 style="margin-bottom: 1.5rem; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-microchip"></i> <?= $h['why_idml_title'] ?>
                </h2>
                <p style="line-height: 1.8; font-size: 1.1rem; color: var(--text-main);"><?= $h['why_idml_desc'] ?></p>
            </section>

            <section class="glass-card security-box">
                <h2 style="margin-bottom: 1.5rem; color: #ff3b30; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-shield-halved"></i> <?= $h['security_title'] ?>
                </h2>
                <p style="color: var(--text-dim); font-size: 1.05rem; line-height: 1.6;"><?= $h['security_desc'] ?></p>
            </section>
            
            <div style="margin-top: 5rem; text-align: center;">
                <a href="dashboard.php" class="apple-btn" style="padding: 18px 50px; font-size: 1.1rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 113, 227, 0.3);">
                    <?= $strings[$ui_lang]['homepage']['cta_start'] ?>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
