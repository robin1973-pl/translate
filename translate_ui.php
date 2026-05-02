<?php 
include 'auth.php'; // translate_ui.php
// translate_ui.php – premium dark mode UI with glassmorphism

$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

// UI language setup
$ui_lang = 'pl';
$ui = $strings[$ui_lang]['translate'];
$ui_index = $strings[$ui_lang]['index'];

$lang = $_GET['lang'] ?? $config['default_lang'];
$csvFile = $config['csv_dir'] . 'translated.csv';
$original_idml = $_GET['original_idml'] ?? 'IDML File';

if (!file_exists($csvFile)) {
    die("Brak danych do tłumaczenia. Wróć do strony głównej.");
}

$rows = array_map('str_getcsv', file($csvFile));
$header = array_shift($rows);

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

?><!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ui['title'] ?> | IDML Translator</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { 
            min-height: 100vh;
            margin: 0;
            padding-bottom: 5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: var(--bg-nav);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        .logo {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--accent);
            text-decoration: none;
            letter-spacing: -1px;
        }
        .logo span { color: var(--text-main); }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .project-meta {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .project-title { margin: 0; font-size: 1.8rem; color: var(--text-main); font-weight: 800; }
        .project-title span { color: var(--accent); font-weight: 600; font-size: 1rem; margin-left: 0.5rem; }

        .card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .table th {
            background: var(--bg-body);
            text-align: left;
            padding: 1.2rem 1rem;
            font-weight: 700;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }
        .table td {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: top;
            background: var(--glass);
        }
        .table tr:hover td { background: var(--bg-body); }

        .original-text { color: var(--text-main); line-height: 1.6; font-weight: 500; }
        .textarea-box {
            width: 100%;
            background: var(--bg-body);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            padding: 1rem;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            min-height: 80px;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .textarea-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }

        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-nav);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
            z-index: 200;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.05);
        }
        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            text-decoration: none;
            font-size: 1rem;
        }
        .btn-primary {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 14px 0 rgba(8, 145, 178, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); background: var(--accent-hover); }
        
        .btn-outline {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--glass-border);
        }
        .btn-outline:hover { background: var(--glass); border-color: var(--accent); }

        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 450px;
            background: var(--glass);
            backdrop-filter: blur(30px);
            border: 1px solid var(--accent);
            border-radius: 24px;
            padding: 2.5rem;
            z-index: 1000;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            text-align: center;
        }
        .progress-container {
            background: var(--bg-body);
            border-radius: 10px;
            height: 10px;
            margin: 2rem 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            width: 0%;
            background: var(--accent);
            box-shadow: 0 0 10px var(--accent);
            transition: width 0.3s ease;
        }
        #loader-log {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--text-dim);
            max-height: 120px;
            overflow-y: auto;
            text-align: left;
            background: var(--bg-body);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
        }

        .hidden-token { display: none; }
        .warning-row { background: rgba(251, 191, 36, 0.05) !important; }
        .warning-text { border-color: #f59e0b !important; }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">TRANSLATE<span>.PRO</span></a>
        <div style="font-size: 0.9rem; color: var(--text-dim); font-weight: 600;">
            <i class="fa-solid fa-file-code"></i> <?= htmlspecialchars($original_idml) ?>
        </div>
    </header>

    <div class="container">
        <div class="project-meta">
            <div>
                <h1 class="project-title">Panel Tłumaczenia<span><i class="fa-solid fa-arrow-right"></i> <?= strtoupper($lang) ?></span></h1>
            </div>
            <div style="font-size: 0.9rem; color: var(--text-dim);">
                <i class="fa-solid fa-list-check"></i> Razem segmentów: <b><?= count($rows) ?></b>
            </div>
        </div>

        <form method="POST" action="apply_translation.php" id="translateForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
            <input type="hidden" name="original_idml" value="<?= htmlspecialchars($original_idml) ?>">

            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 15%">Plik</th>
                            <th style="width: 40%">Oryginał</th>
                            <th style="width: 45%">Tłumaczenie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $row): list($file, $idx, $original, $translated) = $row; ?>
                        <?php
                            $tokens = [];
                            $textWithoutTokens = $original;
                            if (preg_match_all('/\{__(LEAD|TRAIL)SPACES__.*?__\}/', $original, $matches)) {
                                $tokens = $matches[0];
                                foreach ($tokens as $token) {
                                    $textWithoutTokens = str_replace($token, '', $textWithoutTokens);
                                }
                            }
                        ?>
                        <tr id="row-<?= $i ?>">
                            <td style="color: var(--accent); font-family: monospace; font-size: 0.75rem;"><?= htmlspecialchars($file) ?></td>
                            <td>
                                <?php foreach ($tokens as $token): ?>
                                    <span class="hidden-token"><?= htmlspecialchars($token) ?></span>
                                <?php endforeach; ?>
                                <div class="original-text"><?= htmlspecialchars($textWithoutTokens) ?></div>
                            </td>
                            <td>
                                <textarea name="translated[<?= $i ?>]" class="textarea-box" oninput="checkLength(this, <?= $i ?>)"><?= htmlspecialchars($translated) ?></textarea>
                                <input type="hidden" name="original[<?= $i ?>]" value="<?= htmlspecialchars($original) ?>">
                                <input type="hidden" name="file[<?= $i ?>]" value="<?= htmlspecialchars($file) ?>">
                                <input type="hidden" name="index[<?= $i ?>]" value="<?= htmlspecialchars($idx) ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="action-bar">
                <button type="button" class="btn btn-outline" onclick="autoTranslate()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> <?= $ui['auto_translate'] ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-down"></i> <?= $ui['download'] ?>
                </button>
            </div>
        </form>
    </div>

    <div id="loader">
        <h3 style="margin-top: 0;"><?= $ui['processing'] ?></h3>
        <p id="loader-status" style="font-size: 0.9rem; color: var(--text-dim);">Przygotowywanie...</p>
        <div class="progress-container">
            <div id="loader-progress" class="progress-bar"></div>
        </div>
        <div id="loader-log"></div>
    </div>

    <script>
        const targetLang = '<?= $lang ?>';
        
        function checkLength(textarea, index) {
            const row = document.getElementById('row-' + index);
            const original = textarea.closest('tr').querySelector('.original-text').innerText;
            if (textarea.value.length > original.length * 1.5) {
                textarea.classList.add('warning-text');
                row.classList.add('warning-row');
            } else {
                textarea.classList.remove('warning-text');
                row.classList.remove('warning-row');
            }
        }

        async function translateText(text) {
            try {
                const response = await fetch('api/translate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text, targetLang })
                });

                if (!response.ok) throw new Error(`Server error: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                
                return { 
                    text: data.translated, 
                    fromCache: data.source === 'cache' 
                };
            } catch (error) { throw error; }
        }

        async function autoTranslate() {
            const loader = document.getElementById('loader');
            const loaderStatus = document.getElementById('loader-status');
            const loaderProgress = document.getElementById('loader-progress');
            const loaderLog = document.getElementById('loader-log');
            
            loader.style.display = 'block';
            const textareas = document.querySelectorAll('.textarea-box');
            const tasks = [];
            
            textareas.forEach((ta, i) => {
                const original = ta.closest('tr').querySelector('input[name="original['+i+']"]').value;
                if (!ta.value.trim() && original.trim()) {
                    tasks.push({ textarea: ta, original, index: i });
                }
            });

            if (tasks.length === 0) {
                alert("Wszystkie pola są już wypełnione!");
                loader.style.display = 'none';
                return;
            }

            const batchSize = 3;
            for (let i = 0; i < tasks.length; i += batchSize) {
                const batch = tasks.slice(i, i + batchSize);
                const progress = Math.round((i / tasks.length) * 100);
                loaderStatus.innerText = `Przetwarzanie: ${i} / ${tasks.length}`;
                loaderProgress.style.width = `${progress}%`;

                await Promise.allSettled(batch.map(async (task) => {
                    try {
                        const res = await translateText(task.original);
                        
                        const div = document.createElement('div');
                        const icon = res.fromCache ? '💾' : '🌐';
                        div.innerHTML = `<span title="${res.fromCache ? 'Z cache' : 'Z API'}">${icon}</span> ${task.original.substring(0, 25)}...`;
                        loaderLog.prepend(div);

                        task.textarea.value = res.text;
                        checkLength(task.textarea, task.index);
                    } catch (e) {
                        console.error(e);
                        const div = document.createElement('div');
                        div.innerHTML = `<span style="color: #ef4444">❌ Błąd: ${task.original.substring(0, 15)}...</span>`;
                        loaderLog.prepend(div);
                    }
                }));
            }

            loaderProgress.style.width = '100%';
            loaderStatus.innerText = "Zakończono!";
            setTimeout(() => { loader.style.display = 'none'; }, 1500);
        }
    </script>
</body>
</html>