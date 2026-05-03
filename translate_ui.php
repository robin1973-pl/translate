<?php 
include 'auth.php'; // translate_ui.php
// translate_ui.php – premium dark mode UI with glassmorphism

$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';
require_once 'helpers/i18n.php';
require_once 'helpers/workspace.php';

$ui_lang = get_user_language();
$ui = $strings[$ui_lang]['translate'];
$ui_index = $strings[$ui_lang]['index'];

$lang = $_GET['lang'] ?? $config['default_lang'];
$job_id = (int)($_GET['job_id'] ?? 0);
$filePath = $_GET['file'] ?? '';
$original_filename = $filePath ? basename($filePath) : ($_GET['original_idml'] ?? 'Dokument');
$user_id = (int)$_SESSION['user_id'];

// Load CSV from isolated workspace (new) or legacy global path (fallback)
if ($job_id > 0) {
    $csvFile = get_csv_path($user_id, $job_id);
} else {
    // Legacy fallback for old jobs without workspace
    $csvFile = $config['csv_dir'] . 'translated.csv';
}

if (!file_exists($csvFile)) {
    die($ui['error_no_csv']);
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
        
        /* Modern Processing Overlay */
        #processingOverlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.4s ease;
        }
        body.dark-mode #processingOverlay {
            background: rgba(15, 23, 42, 0.8);
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .spinner-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
        }
        .spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .spinner-ring:nth-child(2) {
            width: 80%; height: 80%; top: 10%; left: 10%;
            border-top-color: #22d3ee;
            animation-duration: 0.8s;
            animation-direction: reverse;
        }
        .spinner-icon {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: var(--accent);
            animation: pulse 2s infinite;
        }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 50% { opacity: 0.5; transform: translate(-50%, -50%) scale(0.9); } }

        .processing-text {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }
        .processing-sub {
            color: var(--text-dim);
            margin-top: 8px;
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
<body class="<?= $_SESSION['theme'] ?? '' ?>">
    <!-- Processing Overlay -->
    <div id="processingOverlay">
        <div class="spinner-container">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-icon">
                <i class="fas fa-magic"></i>
            </div>
        </div>
        <div class="processing-text"><?= $ui['overlay_title'] ?></div>
        <div class="processing-sub"><?= $ui['overlay_sub'] ?></div>
    </div>
    <header class="header">
        <a href="dashboard.php" class="logo">INDD <span>TRANSLATION</span></a>
        <div style="font-size: 0.9rem; color: var(--text-dim); font-weight: 600;">
            <i class="fa-solid fa-file-code"></i> <?= htmlspecialchars($original_filename) ?>
        </div>
    </header>

    <div class="container">
        <div class="project-meta">
            <div>
                <h1 class="project-title"><?= $ui['title'] ?> <span><i class="fa-solid fa-arrow-right"></i> <?= strtoupper($lang) ?></span></h1>
            </div>
            <div style="font-size: 0.9rem; color: var(--text-dim);">
                <i class="fa-solid fa-list-check"></i> <?= $ui['segments'] ?>: <b><?= count($rows) ?></b>
            </div>
        </div>

        <?php
        $applyAction = (strtolower(pathinfo($original_filename, PATHINFO_EXTENSION)) === 'idml') 
            ? 'apply_translation.php' 
            : 'apply_office_translation.php';
        ?>
        <form method="POST" action="<?= $applyAction ?>" id="translateForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
            <input type="hidden" name="original_idml" value="<?= htmlspecialchars($original_filename) ?>">
            <input type="hidden" name="file_path" value="<?= htmlspecialchars($filePath) ?>">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_id) ?>">

            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 15%"><?= $ui['table_file'] ?></th>
                            <th style="width: 40%"><?= $ui['table_original'] ?></th>
                            <th style="width: 45%"><?= $ui['table_translated'] ?></th>
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
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-dim);"><?= $ui['context_label'] ?>:</label>
                    <select id="translationContext" class="textarea-box" style="width: auto; padding: 8px 12px; margin: 0;">
                        <option value="general"><?= $ui['context_general'] ?></option>
                        <option value="tech"><?= $ui['context_tech'] ?></option>
                        <option value="marketing"><?= $ui['context_marketing'] ?></option>
                        <option value="legal"><?= $ui['context_legal'] ?></option>
                        <option value="lit"><?= $ui['context_lit'] ?></option>
                    </select>
                </div>
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
        <p id="loader-status" style="font-size: 0.9rem; color: var(--text-dim);"></p>
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

        async function translateText(text, context = 'general') {
            try {
                const response = await fetch('api/translate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text, targetLang, context })
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
            const overlay = document.getElementById('processingOverlay');
            
            overlay.style.display = 'flex';
            const textareas = document.querySelectorAll('.textarea-box');
            const tasks = [];
            
            textareas.forEach((ta, i) => {
                const original = ta.closest('tr').querySelector('input[name="original['+i+']"]').value;
                if (!ta.value.trim() && original.trim()) {
                    tasks.push({ textarea: ta, original, index: i });
                }
            });

            if (tasks.length === 0) {
                alert("<?= $ui['msg_all_filled'] ?>");
                overlay.style.display = 'none';
                return;
            }

            const batchSize = 3;
            for (let i = 0; i < tasks.length; i += batchSize) {
                const batch = tasks.slice(i, i + batchSize);
                const progress = Math.round((i / tasks.length) * 100);
                loaderStatus.innerText = `<?= $ui['msg_processing'] ?>: ${i} / ${tasks.length}`;
                loaderProgress.style.width = `${progress}%`;

                await Promise.allSettled(batch.map(async (task) => {
                    try {
                        const context = document.getElementById('translationContext').value;
                        const res = await translateText(task.original, context);
                        
                        const div = document.createElement('div');
                        const icon = res.fromCache ? '💾' : '🌐';
                        const sourceTitle = res.fromCache ? '<?= $ui['msg_cache'] ?>' : '<?= $ui['msg_api'] ?>';
                        div.innerHTML = `<span title="${sourceTitle}">${icon}</span> ${task.original.substring(0, 25)}...`;
                        loaderLog.prepend(div);

                        task.textarea.value = res.text;
                        checkLength(task.textarea, task.index);
                    } catch (e) {
                        console.error(e);
                        const div = document.createElement('div');
                        div.innerHTML = `<span style="color: #ef4444">❌ <?= $ui['msg_error'] ?>: ${task.original.substring(0, 15)}...</span>`;
                        loaderLog.prepend(div);
                    }
                }));
            }

            overlay.style.display = 'none';
            alert("<?= $ui['msg_finished'] ?>");
        }
    </script>
</body>
</html>