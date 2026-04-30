<?php
// translate_ui.php – z obsługą CSRF, auto-tłumaczeniem i wielojęzycznym słownikiem UI
session_start();
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';
require_once 'helpers/format_helpers.php';

// Obsługa zmiany języka interfejsu (UI)
if (isset($_GET['ui_lang'])) {
    $_SESSION['ui_lang'] = $_GET['ui_lang'];
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query(array_diff_key($_GET, ['ui_lang' => ''])));
    exit;
}

$ui_lang = $_SESSION['ui_lang'] ?? $config['ui_lang'] ?? 'pl';
if (!isset($strings[$ui_lang])) $ui_lang = 'pl'; // fallback

$ui = $strings[$ui_lang]['translate_ui'];
$lang = $_GET['lang'] ?? $config['default_lang'];
$csvFile = $config['csv_dir'] . 'translated.csv';
$original_idml = $_GET['original_idml'] ?? '';

if (!file_exists($csvFile)) {
    die($ui['messages']['no_data']);
}

$rows = array_map('str_getcsv', file($csvFile));
$header = array_shift($rows);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

?><!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($ui['title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="helpers/style_premium.css">
  <style>
    #loader { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; width: 450px; }
    #loader-log { font-size: 10px; line-height: 1.2; }
  </style>
</head>
<body>

<div class="container py-4">
  <header class="header-nav">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-sm btn-outline-secondary btn-premium-back"><?= htmlspecialchars($ui['buttons']['back'] ?? '← Back') ?></a>
      <h2 class="brand-title mb-0"><?= htmlspecialchars($ui['header_prefix']) ?> <b><?= htmlspecialchars(strtoupper($lang)) ?></b></h2>
    </div>
    <div class="lang-switcher">
      <a href="?<?= http_build_query(array_merge($_GET, ['ui_lang' => 'pl'])) ?>" class="lang-btn <?= ($ui_lang === 'pl') ? 'active' : '' ?>">PL</a>
      <a href="?<?= http_build_query(array_merge($_GET, ['ui_lang' => 'en'])) ?>" class="lang-btn <?= ($ui_lang === 'en') ? 'active' : '' ?>">EN</a>
    </div>
  </header>

  <form method="POST" action="apply_translation.php" id="translateForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
    <input type="hidden" name="original_idml" value="<?= htmlspecialchars($original_idml) ?>">

    <div class="table-responsive">
      <table class="premium-table mb-4" style="table-layout: fixed;">
        <thead>
          <tr>
            <th style="width: 10%;"><?= htmlspecialchars($ui['table']['file']) ?></th>
            <th style="width: 5%;"><?= htmlspecialchars($ui['table']['index']) ?></th>
            <th style="width: 42.5%;"><?= htmlspecialchars($ui['table']['original']) ?></th>
            <th style="width: 42.5%;"><?= htmlspecialchars($ui['table']['translation']) ?></th>
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
          <tr>
            <td class="text-premium-muted small" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></td>
            <td class="text-premium-muted small"><?= htmlspecialchars($idx) ?></td>
            <td>
              <?php foreach ($tokens as $token): ?>
                <span class="hidden-token"><?= htmlspecialchars($token) ?></span>
              <?php endforeach; ?>
              <div style="font-size: 0.95rem;"><?= htmlspecialchars($textWithoutTokens) ?></div>
            </td>
            <td>
              <textarea name="translated[<?= $i ?>]" class="form-control" rows="2" placeholder="..."><?= htmlspecialchars($translated) ?></textarea>
              <input type="hidden" name="original[<?= $i ?>]" value="<?= htmlspecialchars($original) ?>">
              <input type="hidden" name="file[<?= $i ?>]" value="<?= htmlspecialchars($file) ?>">
              <input type="hidden" name="index[<?= $i ?>]" value="<?= htmlspecialchars($idx) ?>">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-5">
      <div class="text-premium-muted">
        Total items: <strong><?= count($rows) ?></strong>
      </div>
      <div class="d-flex gap-3">
        <button type="button" class="btn btn-outline-premium px-4" onclick="autoTranslate()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-magic me-2" viewBox="0 0 16 16">
                <path d="M9.5 2.672a.5.5 0 1 0 1 0V.843a.5.5 0 0 0-1 0zm4.5.03a.5.5 0 0 0 0 1h1.83a.5.5 0 0 0 0-1zM1.866 6.562a.5.5 0 1 1 .707.708l-1.414 1.414a.5.5 0 1 1-.707-.708zM12.674 1h-1.83a.5.5 0 0 0 0 1h1.83a.5.5 0 0 0 0-1zM2.5 14.5a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707.707zm11-11a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707.707zM14.5 9.5a.5.5 0 0 1 0 1h-1.83a.5.5 0 0 1 0-1zM2.5 1.5a.5.5 0 0 1 .707-.707L1.866.086a.5.5 0 1 1-.707.707zM9.5 14.5a.5.5 0 0 1 0-1h1.83a.5.5 0 0 1 0 1z"/>
                <path d="M7.035 4.403l.729-.268a1.82 1.82 0 0 1 2.456 2.456l-.268.729a1.82 1.82 0 0 1-2.456-2.456zM4.403 7.035l.729-.268a1.82 1.82 0 0 1 2.456 2.456l-.268.729a1.82 1.82 0 0 1-2.456-2.456z"/>
            </svg>
            <?= htmlspecialchars($ui['buttons']['auto_translate']) ?>
        </button>
        <button type="submit" class="btn btn-premium px-5 shadow-sm"><?= htmlspecialchars($ui['buttons']['download_idml']) ?></button>
      </div>
    </div>
  </form>
</div>

<div id="loader" class="card shadow-lg">
  <div class="card-body text-center p-4">
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
      <span class="visually-hidden"><?= htmlspecialchars($ui['loader']['title']) ?></span>
    </div>
    <h4 class="card-title mb-1"><?= htmlspecialchars($ui['loader']['title']) ?></h4>
    <p id="loader-status" class="text-premium-muted mb-4"><?= htmlspecialchars($ui['loader']['status_preparing']) ?></p>
    <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
      <div id="loader-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
    </div>
    <div id="loader-log" class="text-start small overflow-auto" style="max-height: 120px; font-family: 'Inter', sans-serif; background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);"></div>
  </div>
</div>

<script>
const targetLang = '<?= $lang ?>';
const uiStrings = <?= json_encode($ui) ?>;

async function logClientError(errorData) {
    try {
        await fetch('log_client_error.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(errorData)
        });
    } catch (e) {
        console.error('Błąd logowania:', e);
    }
}

function shouldSkipTranslation(text, skipTokens, preserveMap) {
    const trimmed = text.trim();
    if (/^\s*([0-9]|[:;\-–—•*\.…()])+\s*$/.test(text)) return true;
    if (/^CH\s*\d*$/i.test(trimmed) || /^SL\s*\d*$/i.test(trimmed)) return true;
    if (skipTokens.includes(trimmed)) return true;
    return Object.keys(preserveMap).some(key => 
        new RegExp(`^${key}(\\s?\\d*)$`, 'i').test(trimmed)
    );
}

async function translateText(text, skipTokens, preserveMap) {
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
                        content: `You are a professional translator for lighting and DJ equipment manuals. 
                        Translate accurately and concisely into ${targetLang}, keeping similar length to the source. 
                        Do not translate brand names or technical abbreviations (ON, OFF, Auto, Tilt Fine, Slave).
                        IMPORTANT: Preserve all technical symbols and units like °, ², ³, ₀, ₁, ₂, ₊, ⁻ as they are critical for technical specifications.
                        Never translate DMX channel names like "CH 1" or "SL 1".`
                    },
                    { role: 'user', content: `Translate this into ${targetLang}:\n\n${text}` }
                ],
                temperature: 0.3
            })
        });

        if (!response.ok) throw new Error(`API error: ${response.status}`);
        const data = await response.json();
        let translated = data.choices[0].message.content.trim();
        
        if (translated.includes('Nie mogę') || translated.toLowerCase().includes('i\'m sorry')) {
            return text; 
        }
        return translated;
    } catch (error) {
        throw error;
    }
}

async function autoTranslate() {
    const loader = document.getElementById('loader');
    const loaderStatus = document.getElementById('loader-status');
    const loaderProgress = document.getElementById('loader-progress');
    const loaderLog = document.getElementById('loader-log');
    
    loader.style.display = 'block';
    loaderLog.innerHTML = '';

    const form = document.getElementById('translateForm');
    const textareas = form.querySelectorAll('textarea');
    const skipTokens = <?= json_encode($config['skip_tokens'] ?? []) ?>;
    const preserveMap = <?= json_encode($config['preserveMap'] ?? []) ?>;

    const translationTasks = [];
    Array.from(textareas).forEach((textarea, i) => {
        const originalInput = form.querySelector(`input[name="original[${i}]"]`);
        const originalText = originalInput?.value || '';
        if (textarea.value.trim() || !originalText.trim()) return;
        if (shouldSkipTranslation(originalText, skipTokens, preserveMap)) {
            textarea.value = originalText;
            return;
        }
        translationTasks.push({ textarea, originalText, i });
    });

    const batchSize = 5; 
    let successCount = 0;
    let errorCount = 0;

    for (let i = 0; i < translationTasks.length; i += batchSize) {
        const batch = translationTasks.slice(i, i + batchSize);
        const progress = Math.round((i / translationTasks.length) * 100);
        loaderStatus.innerText = `Processing: ${i} / ${translationTasks.length}`;
        loaderProgress.style.width = `${progress}%`;
        
        await Promise.allSettled(
            batch.map(async ({ textarea, originalText, i }) => {
                try {
                    const logEntry = document.createElement('div');
                    logEntry.innerText = `${uiStrings.loader.log_prefix} ${originalText.substring(0, 30)}...`;
                    loaderLog.prepend(logEntry);

                    const translated = await translateText(originalText, skipTokens, preserveMap);
                    textarea.value = translated;
                    successCount++;
                } catch (error) {
                    const logEntry = document.createElement('div');
                    logEntry.className = 'text-danger';
                    logEntry.innerText = `${uiStrings.loader.error_prefix} ${error.message}`;
                    loaderLog.prepend(logEntry);
                    textarea.value = originalText;
                    errorCount++;
                }
            })
        );

        if (i + batchSize < translationTasks.length) {
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }

    loaderProgress.style.width = `100%`;
    loaderStatus.innerText = `Done: ${successCount} OK, ${errorCount} Errors`;
    setTimeout(() => { loader.style.display = 'none'; }, 2000);
    
    let alertMsg = uiStrings.messages.finish_alert
        .replace('{lang}', targetLang)
        .replace('{success}', successCount)
        .replace('{error}', errorCount);
    alert(alertMsg);
}

function highlightLongTranslations() {
    const form = document.getElementById('translateForm');
    const textareas = form.querySelectorAll('textarea');
    textareas.forEach((textarea, i) => {
        const originalInput = form.querySelector(`input[name="original[${i}]"]`);
        const originalText = originalInput?.value || '';
        const translatedText = textarea.value.trim();
        if (originalText && translatedText.length > originalText.length * 1.5) {
            textarea.classList.add('bg-warning-subtle', 'border', 'border-warning');
            textarea.title = uiStrings.messages.too_long_title;
        } else {
            textarea.classList.remove('bg-warning-subtle', 'border', 'border-warning');
            textarea.removeAttribute('title');
        }
    });
}

window.addEventListener('DOMContentLoaded', highlightLongTranslations);
</script>
</body>
</html>