<?php
session_start();
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language(['pl', 'en', 'de']);
$ui = $strings[$ui_lang]['homepage'];
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ui['hero_title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            /* Professional Light Mode (Default) */
            --bg-body: #f8fafc;
            --bg-nav: rgba(248, 250, 252, 0.9);
            --text-main: #0f172a;
            --text-dim: #475569;
            --accent: #0891b2;
            --accent-hover: #0e7490;
            --glass: #ffffff;
            --glass-border: #e2e8f0;
            --btn-text: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body.dark-mode {
            /* Professional Dark Mode */
            --bg-body: #0f172a;
            --bg-nav: rgba(15, 23, 42, 0.9);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --accent: #22d3ee;
            --accent-hover: #67e8f9;
            --glass: #1e293b;
            --glass-border: #334155;
            --btn-text: #0f172a;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        .background-blobs {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            overflow: hidden;
            opacity: 0.5;
        }
        .blob {
            position: absolute;
            width: 800px; height: 800px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            filter: blur(100px);
            opacity: 0.15;
            border-radius: 50%;
            animation: move 25s infinite alternate;
        }
        @keyframes move {
            from { transform: translate(-20%, -20%); }
            to { transform: translate(30%, 30%); }
        }

        nav {
            padding: 1.2rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-nav);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }
        .logo { font-size: 1.6rem; font-weight: 800; color: var(--accent); letter-spacing: -1px; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: var(--accent); }
        
        .theme-toggle {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.1rem;
            box-shadow: var(--shadow);
        }
        .theme-toggle:hover { transform: translateY(-2px); border-color: var(--accent); }
        
        .btn-login {
            background: var(--accent);
            color: var(--btn-text) !important;
            padding: 0.6rem 1.8rem;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 4px 14px 0 rgba(8, 145, 178, 0.39);
        }

        header {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 4rem 10%;
        }
        h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            font-weight: 800;
            color: var(--text-main);
        }
        p.subtitle {
            font-size: 1.4rem;
            color: var(--text-dim);
            max-width: 800px;
            margin-bottom: 3.5rem;
            font-weight: 500;
        }
        .cta-group { display: flex; gap: 1.2rem; flex-wrap: wrap; justify-content: center; }
        .btn {
            padding: 1.1rem 3rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid transparent;
        }
        .btn-primary {
            background: var(--accent);
            color: var(--btn-text);
            box-shadow: 0 10px 20px -10px rgba(8, 145, 178, 0.5);
        }
        .btn-primary:hover { 
            transform: translateY(-3px); 
            background: var(--accent-hover);
            box-shadow: 0 15px 25px -10px rgba(8, 145, 178, 0.6);
        }
        .btn-secondary {
            background: var(--glass);
            color: var(--text-main);
            border: 2px solid var(--glass-border);
            box-shadow: var(--shadow);
        }
        .btn-secondary:hover { 
            border-color: var(--accent);
            transform: translateY(-3px);
        }

        .features {
            padding: 6rem 10%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            background: var(--bg-body);
        }
        .feature-card {
            background: var(--glass);
            padding: 3rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s;
            box-shadow: var(--shadow);
        }
        .feature-card:hover { transform: translateY(-8px); border-color: var(--accent); }
        .feature-card i { font-size: 2.5rem; color: var(--accent); margin-bottom: 1.5rem; }
        .feature-card h3 { font-size: 1.6rem; margin-bottom: 1rem; font-weight: 700; }
        .feature-card p { color: var(--text-dim); font-size: 1.1rem; }

        footer {
            text-align: center;
            padding: 4rem 10%;
            color: var(--text-dim);
            font-weight: 600;
            border-top: 1px solid var(--glass-border);
            background: var(--bg-body);
        }
    </style>
</head>
<body>
    <div class="background-blobs">
        <div class="blob"></div>
    </div>

    <nav>
        <div class="logo">TRANSLATE.PRO</div>
        <div class="nav-links">
            <button class="theme-toggle" id="themeBtn" title="Przełącz motyw">
                <i class="fas fa-moon"></i>
            </button>
            <a href="demo.php"><?= $ui['cta_demo'] ?></a>
            <a href="login.php" class="btn-login"><?= $ui['login'] ?></a>
            <a href="register.php"><?= $ui['register'] ?></a>
        </div>
    </nav>

    <header>
        <h1><?= $ui['hero_title'] ?></h1>
        <p class="subtitle"><?= $ui['hero_subtitle'] ?></p>
        <div class="cta-group">
            <a href="register.php" class="btn btn-primary"><?= $ui['cta_start'] ?></a>
            <a href="demo.php" class="btn btn-secondary"><?= $ui['cta_demo'] ?></a>
        </div>
    </header>

    <section class="features">
        <div class="feature-card">
            <i class="fas fa-file-invoice"></i>
            <h3><?= $ui['feat_idml'] ?></h3>
            <p><?= $ui['feat_idml_desc'] ?></p>
        </div>
        <div class="feature-card">
            <i class="fas fa-file-word"></i>
            <h3><?= $ui['feat_office'] ?></h3>
            <p><?= $ui['feat_office_desc'] ?></p>
        </div>
        <div class="feature-card">
            <i class="fas fa-brain"></i>
            <h3><?= $ui['feat_ai'] ?></h3>
            <p><?= $ui['feat_ai_desc'] ?></p>
        </div>
    </section>

    <section id="pricing" style="padding: 8rem 10%; text-align: center;">
        <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 4rem;"><?= $strings[$ui_lang]['pricing']['title'] ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto;">
            
            <!-- Starter -->
            <div class="feature-card" style="display: flex; flex-direction: column; align-items: center; border-radius: 30px;">
                <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;"><?= $strings[$ui_lang]['pricing']['starter_name'] ?></span>
                <div style="font-size: 3.5rem; font-weight: 800; margin: 1.5rem 0;"><?= $strings[$ui_lang]['pricing']['starter_price'] ?></div>
                <ul style="list-style: none; text-align: left; margin-bottom: 2.5rem; width: 100%;">
                    <?php foreach ($strings[$ui_lang]['pricing']['starter_feat'] as $f): ?>
                        <li style="margin-bottom: 1rem; color: var(--text-dim); font-weight: 500;"><i class="fas fa-check" style="color: var(--accent); margin-right: 0.8rem;"></i> <?= $f ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="register.php" class="btn btn-secondary" style="width: 100%;"><?= $strings[$ui_lang]['pricing']['cta'] ?></a>
            </div>

            <!-- Pro -->
            <div class="feature-card" style="display: flex; flex-direction: column; align-items: center; border-radius: 30px; border: 2px solid var(--accent); position: relative; transform: scale(1.05); z-index: 10; background: var(--glass);">
                <div style="position: absolute; top: -15px; background: var(--accent); color: white; padding: 0.4rem 1.2rem; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">Najczęściej wybierany</div>
                <span style="color: var(--accent); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;"><?= $strings[$ui_lang]['pricing']['pro_name'] ?></span>
                <div style="font-size: 3.5rem; font-weight: 800; margin: 1.5rem 0;"><?= $strings[$ui_lang]['pricing']['pro_price'] ?><span style="font-size: 1rem; color: var(--text-dim);">/msc</span></div>
                <ul style="list-style: none; text-align: left; margin-bottom: 2.5rem; width: 100%;">
                    <?php foreach ($strings[$ui_lang]['pricing']['pro_feat'] as $f): ?>
                        <li style="margin-bottom: 1rem; color: var(--text-main); font-weight: 600;"><i class="fas fa-check" style="color: var(--accent); margin-right: 0.8rem;"></i> <?= $f ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="register.php" class="btn btn-primary" style="width: 100%;"><?= $strings[$ui_lang]['pricing']['cta'] ?></a>
            </div>

            <!-- Enterprise -->
            <div class="feature-card" style="display: flex; flex-direction: column; align-items: center; border-radius: 30px;">
                <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;"><?= $strings[$ui_lang]['pricing']['enterprise_name'] ?></span>
                <div style="font-size: 3.5rem; font-weight: 800; margin: 1.5rem 0;"><?= $strings[$ui_lang]['pricing']['enterprise_price'] ?></div>
                <ul style="list-style: none; text-align: left; margin-bottom: 2.5rem; width: 100%;">
                    <?php foreach ($strings[$ui_lang]['pricing']['enterprise_feat'] as $f): ?>
                        <li style="margin-bottom: 1rem; color: var(--text-dim); font-weight: 500;"><i class="fas fa-check" style="color: var(--accent); margin-right: 0.8rem;"></i> <?= $f ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="mailto:contact@indd-translation.com" class="btn btn-secondary" style="width: 100%;">Kontakt</a>
            </div>
        </div>
    </section>

    <footer>
        &copy; 2026 Translate.pro - Premium IDML Solutions
    </footer>

    <script>
        const themeBtn = document.getElementById('themeBtn');
        const body = document.body;
        const icon = themeBtn.querySelector('i');

        // Light is default. Check if user wants dark.
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }

        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            
            if (isDark) {
                icon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html>
