<?php
session_start();
$config = require 'config.php';
$languages = require 'helpers/languages.php';

$options = [
    'tadeusz' => [
        'title' => 'Pan Tadeusz (Mickiewicz)',
        'original' => "Litwo! Ojczyzno moja! ty jesteś jak zdrowie:\nIle cię trzeba cenić, ten tylko się dowie,\nKto cię stracił. Dziś piękność twą w całej ozdobie\nWidzę i opisuję, bo tęsknię po tobie.",
        'target_lang' => 'en'
    ],
    'shakespeare' => [
        'title' => 'Sonnet 18 (Shakespeare)',
        'original' => "Shall I compare thee to a summer’s day?\nThou art more lovely and more temperate:\nRough winds do shake the darling buds of May,\nAnd summer’s lease hath all too short a date.",
        'target_lang' => 'pl'
    ]
];

$selection = $_GET['v'] ?? 'tadeusz';
if (!isset($options[$selection])) $selection = 'tadeusz';

$target_lang_code = $_GET['lang'] ?? $options[$selection]['target_lang'];
$current = $options[$selection];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Demo AI - Translate.pro</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .demo-container {
            max-width: 1000px;
            margin: 4rem auto;
            padding: 0 1rem;
        }
        .translation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .text-area {
            min-height: 200px;
            padding: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
            border-radius: 16px;
            background: var(--border);
            color: var(--text-main);
            border: 1px solid transparent;
            transition: var(--transition);
        }
        .text-area:focus {
            border-color: var(--accent);
            background: var(--bg-card);
        }
        .loader {
            display: none;
            text-align: center;
            margin: 2rem 0;
        }
        .pulse {
            animation: pulse 1.5s infinite ease-in-out;
        }
        @keyframes pulse {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }
    </style>
</head>
<body style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;">

    <div class="demo-container">
        <header style="text-align: center; margin-bottom: 3rem;">
            <div style="display: inline-flex; align-items: center; gap: 10px; margin-bottom: 1rem;">
                <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-magic"></i>
                </div>
                <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -1px;">Interaktywne Demo AI</h1>
            </div>
            <p style="color: var(--text-dim); font-size: 1.1rem;">Zobacz, jak sztuczna inteligencja GPT-4o radzi sobie z literaturą.</p>
        </header>

        <div class="glass-card">
            <!-- Selector -->
            <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 2rem;">
                <a href="?v=tadeusz&lang=<?= $target_lang_code ?>" class="apple-btn <?= $selection === 'tadeusz' ? '' : 'apple-btn-secondary' ?>">
                    Pan Tadeusz
                </a>
                <a href="?v=shakespeare&lang=<?= $target_lang_code ?>" class="apple-btn <?= $selection === 'shakespeare' ? '' : 'apple-btn-secondary' ?>">
                    Shakespeare
                </a>
            </div>

            <!-- Language Select -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">Język docelowy</label>
                <select id="targetLang" class="apple-input" style="max-width: 250px; text-align: center;" onchange="window.location.href='?v=<?= $selection ?>&lang='+this.value">
                    <?php foreach ($languages as $code => $name): ?>
                        <option value="<?= $code ?>" <?= $code === $target_lang_code ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Button ABOVE Grid for better visibility -->
            <div style="margin-top: 1rem; margin-bottom: 2rem; text-align: center;">
                <button id="translateBtn" class="apple-btn" style="padding: 1.2rem 3rem; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(0, 113, 227, 0.2); width: 100%; max-width: 400px;">
                    <i class="fas fa-wand-magic-sparkles"></i> Uruchom magię tłumaczenia
                </button>
            </div>

            <!-- Grid -->
            <div class="translation-grid">
                <div>
                    <h4 style="margin-bottom: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-align-left" style="color: var(--text-dim);"></i> Oryginał
                    </h4>
                    <div class="text-area"><?= htmlspecialchars($current['original']) ?></div>
                </div>
                <div>
                    <h4 style="margin-bottom: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-wand-sparkles" style="color: var(--accent);"></i> Tłumaczenie AI
                    </h4>
                    <div id="outputBox" class="text-area" style="border: 1px solid var(--border); background: rgba(0,113,227,0.02);">
                        Czekam na uruchomienie silnika...
                    </div>
                </div>
            </div>

            <div id="loader" class="loader">
                <i class="fas fa-circle-notch fa-spin" style="font-size: 2rem; color: var(--accent); margin-bottom: 1rem;"></i>
                <p id="statusText" class="pulse" style="font-weight: 600; color: var(--text-main);">Inicjalizacja GPT-4o...</p>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <a href="dashboard.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-arrow-left"></i> Wróć do Dashboardu
                </a>
            </div>
        </div>
    </div>

    <script>
        const translateBtn = document.getElementById('translateBtn');
        const outputBox = document.getElementById('outputBox');
        const loader = document.getElementById('loader');
        const statusText = document.getElementById('statusText');

        translateBtn.addEventListener('click', function() {
            translateBtn.style.display = 'none';
            loader.style.display = 'block';
            outputBox.innerText = '';
            
            const steps = [
                "Analiza stylu literackiego...",
                "Mapowanie kontekstu poetyckiego...",
                "Generowanie tłumaczenia wysokiej klasy...",
                "Finalizacja szlifów..."
            ];
            
            let i = 0;
            const statusInterval = setInterval(() => {
                if (i < steps.length) {
                    statusText.innerText = steps[i];
                    i++;
                } else {
                    clearInterval(statusInterval);
                }
            }, 1200);

            const formData = new FormData();
            formData.append('v', '<?= $selection ?>');
            formData.append('lang', '<?= $target_lang_code ?>');

            fetch('api_demo_translate.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                if (data.status === 'success') {
                    outputBox.innerText = data.translation;
                    translateBtn.innerHTML = '<i class="fas fa-redo"></i> Tłumacz ponownie';
                    translateBtn.style.display = 'inline-flex';
                } else {
                    outputBox.innerText = "Błąd: " + data.message;
                    translateBtn.style.display = 'inline-flex';
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                outputBox.innerText = "Błąd połączenia z serwerem.";
                translateBtn.style.display = 'inline-flex';
            });
        });
    </script>
</body>
</html>
