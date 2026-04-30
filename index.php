<?php // index.php
session_start();
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

// Obsługa zmiany języka interfejsu (UI)
if (isset($_GET['ui_lang'])) {
    $_SESSION['ui_lang'] = $_GET['ui_lang'];
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$ui_lang = $_SESSION['ui_lang'] ?? $config['ui_lang'] ?? 'pl';
if (!isset($strings[$ui_lang])) $ui_lang = 'pl'; // fallback

$ui = $strings[$ui_lang]['index'];
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($ui['title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="helpers/style_premium.css">
</head>
<body>

<div class="container py-5">
  <header class="header-nav">
    <h1 class="brand-title"><?= htmlspecialchars($ui['welcome_title']) ?></h1>
    <div class="lang-switcher">
      <a href="?ui_lang=pl" class="lang-btn <?= ($ui_lang === 'pl') ? 'active' : '' ?>">PL</a>
      <a href="?ui_lang=en" class="lang-btn <?= ($ui_lang === 'en') ? 'active' : '' ?>">EN</a>
    </div>
  </header>

  <div class="row justify-content-center">
    <div class="col-md-6 mt-4">
      <form action="extract_idml.php" method="POST" enctype="multipart/form-data" class="premium-card">
        <div class="mb-4">
          <label for="idml" class="form-label text-premium-muted"><?= htmlspecialchars($ui['file_label']) ?></label>
          <input type="file" name="idml" id="idml" accept=".idml,.zip" class="form-control" required>
        </div>

        <div class="mb-4">
          <label for="lang" class="form-label text-premium-muted"><?= htmlspecialchars($ui['lang_label']) ?></label>
          <select name="lang" id="lang" class="form-select">
            <?php foreach ($config['available_languages'] as $code => $name): ?>
              <option value="<?= $code ?>" <?= ($code === $config['default_lang']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-premium w-100"><?= htmlspecialchars($ui['submit_btn']) ?></button>
      </form>
    </div>
  </div>
</div>

</body>
</html>