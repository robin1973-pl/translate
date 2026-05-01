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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-deep: #081425;
            --accent: #06B6D4;
            --accent-glow: rgba(6, 182, 212, 0.2);
            --glass: rgba(15, 23, 42, 0.6);
            --glass-card: rgba(15, 23, 42, 0.8);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #d8e3fb;
            --text-dim: #869397;
            --success: #10b981;
        }
        body { 
            background: #081425;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            margin: 0;
            padding-bottom: 5rem; /* Space for footer/actions */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(8, 20, 37, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        .logo span { color: var(--accent); }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .project-meta {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .project-title { margin: 0; font-size: 1.5rem; }
        .project-title span { color: var(--accent); font-weight: 400; font-size: 1rem; margin-left: 0.5rem; }

        /* Translation Table */
        .card {
            background: var(--glass-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .table th {
            background: rgba(255, 255, 255, 0.03);
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            color: var(--text-dim);
            border-bottom: 1px solid var(--glass-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }
        .table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: top;
        }
        .table tr:hover { background: rgba(255, 255, 255, 0.02); }

        .original-text { color: var(--text-dim); line-height: 1.5; }
        .textarea-box {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: white;
            padding: 0.75rem;
            font-family: inherit;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 60px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .textarea-box:focus {
            outline: none;
            border-color: var(--accent);
        }

        /* Action Bar */
        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(8, 20, 37, 0.9);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            z-index: 200;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5); }
        
        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--glass-border);
        }
        .btn-outline:hover { background: rgba(255, 255, 255, 0.1); border-color: var(--accent); }

        /* Loader & Progress */
        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            background: var(--glass-card);
            backdrop-filter: blur(30px);
            border: 1px solid var(--accent);
            border-radius: 20px;
            padding: 2rem;
            z-index: 1000;
            box-shadow: 0 0 50px rgba(6, 182, 212, 0.4);
            text-align: center;
        }
        .progress-container {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            height: 8px;
            margin: 1.5rem 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #06b6d4, #6366f1);
            box-shadow: 0 0 10px var(--accent);
            transition: width 0.3s ease;
        }
        #loader-log {
            font-family: monospace;
            font-size: 0.75rem;
            color: var(--text-dim);
            max-height: 100px;
            overflow-y: auto;
            text-align: left;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem;
            border-radius: 4px;
        }

        .hidden-token { display: none; }
        .warning-row { background: rgba(251, 191, 36, 0.05) !important; }
        .warning-text { border-color: #f59e0b !important; }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php" class="logo">IDML<span>Translator</span></a>
        <div style="font-size: 0.85rem; color: var(--text-dim);">
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
                const response = await fetch('https://api.openai.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer <?= $config["openai_key"] ?>'
                    },
                    body: JSON.stringify({
                        model: 'gpt-4o',
                        messages: [
                            {
                                role: 'system',
                                content: `You are a professional translator. Translate accurately into ${targetLang}. 
                                Preserve all technical symbols like °, ², ³, ₀, ₁, ₂, ₊, ⁻.
                                Do not translate DMX channel names (CH 1, SL 1).`
                            },
                            { role: 'user', content: `Translate this into ${targetLang}:\n\n${text}` }
                        ],
                        temperature: 0.3
                    })
                });

                if (!response.ok) throw new Error(`API error: ${response.status}`);
                const data = await response.json();
                return data.choices[0].message.content.trim();
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
                        const div = document.createElement('div');
                        div.innerText = `> ${task.original.substring(0, 20)}...`;
                        loaderLog.prepend(div);

                        const res = await translateText(task.original);
                        task.textarea.value = res;
                        checkLength(task.textarea, task.index);
                    } catch (e) {
                        console.error(e);
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