<?php
include 'auth.php'; 
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

$ui_lang = 'pl';
$ui = $strings[$ui_lang]['index'];
$username = $_SESSION['username'] ?? 'User';

?><!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ui['title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-deep: #081425;
            --accent: #06B6D4;
            --accent-office: #10b981;
            --accent-glow: rgba(6, 182, 212, 0.2);
            --glass: rgba(15, 23, 42, 0.6);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #d8e3fb;
            --text-dim: #869397;
        }
        body { 
            background: #081425;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: rgba(8, 20, 37, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { font-size: 1.25rem; font-weight: 700; color: white; text-decoration: none; }
        .logo span { color: var(--accent); }

        .user-nav { display: flex; align-items: center; gap: 1.5rem; }
        .btn-logout { color: var(--text-dim); text-decoration: none; font-size: 0.85rem; }
        .btn-logout:hover { color: #f87171; }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem 2rem;
        }

        .title-hero {
            text-align: center;
            margin-bottom: 3rem;
        }
        .title-hero h1 { font-size: 2.5rem; margin-bottom: 0.5rem; letter-spacing: -1px; }
        .title-hero p { color: var(--text-dim); font-size: 1.1rem; }

        .cards-container {
            display: flex;
            gap: 2rem;
            width: 100%;
            max-width: 1100px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card {
            flex: 1;
            min-width: 340px;
            max-width: 500px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }

        .card-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
            margin-bottom: 2rem;
            font-weight: 700;
        }
        .card-idml .card-title i { color: var(--accent); }
        .card-office .card-title i { color: var(--accent-office); }

        .drop-zone {
            border: 2px dashed var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
        }
        .card-idml .drop-zone:hover { border-color: var(--accent); background: rgba(6, 182, 212, 0.05); }
        .card-office .drop-zone:hover { border-color: var(--accent-office); background: rgba(16, 185, 129, 0.05); }

        .drop-zone i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
        .card-idml .drop-zone i { color: var(--accent); }
        .card-office .drop-zone i { color: var(--accent-office); }

        .form-select {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 12px;
            padding: 0.85rem;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }
        .card-idml .btn-submit { 
            background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            box-shadow: 0 10px 20px -5px rgba(6, 182, 212, 0.4);
        }
        .card-office .btn-submit { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .file-info { font-size: 0.8rem; color: var(--accent); margin-top: 0.5rem; display: none; }
        .card-office .file-info { color: var(--accent-office); }

        .ambient-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 1000px;
            height: 1000px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="ambient-bg"></div>
    <header class="header">
        <a href="index.php" class="logo">IDML<span>Translator</span></a>
        <div class="user-nav">
            <span><i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($username) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <?= $ui['logout'] ?></a>
        </div>
    </header>

    <main class="main-content">
        <div class="title-hero">
            <h1>Multi-Format Translator</h1>
            <p>Przetłumacz swoje projekty z zachowaniem stylów i formatowania.</p>
        </div>

        <div class="cards-container">
            <!-- IDML Card -->
            <div class="card card-idml">
                <div class="card-title"><i class="fa-solid fa-layer-group"></i> InDesign (IDML)</div>
                <form action="extract_idml.php" method="POST" enctype="multipart/form-data">
                    <div class="drop-zone" onclick="document.getElementById('idml-input').click()">
                        <i class="fa-solid fa-file-zipper"></i>
                        <p><?= $ui['upload_title'] ?></p>
                        <span style="font-size: 0.8rem; color: var(--text-dim);">Wybierz plik .idml (ZIP)</span>
                        <input type="file" name="idml" id="idml-input" accept=".idml,.zip" style="display:none" onchange="updateLabel(this, 'idml-label')" required>
                        <div id="idml-label" class="file-info"></div>
                    </div>
                    <label style="font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.5rem; display: block; font-weight: 600;">JĘZYK DOCELOWY</label>
                    <select name="lang" class="form-select">
                        <option value="cs" selected>Czeski</option>
                        <option value="de">Niemiecki</option>
                        <option value="sk">Słowacki</option>
                        <option value="hu">Węgierski</option>
                        <option value="es">Hiszpański</option>
                        <option value="fr">Francuski</option>
                        <option value="it">Włoski</option>
                        <option value="pl">Polski</option>
                    </select>
                    <button type="submit" class="btn-submit">Wyodrębnij z IDML</button>
                </form>
            </div>

            <!-- Office Card -->
            <div class="card card-office">
                <div class="card-title"><i class="fa-solid fa-file-word"></i> Office (Docx/Pptx)</div>
                <form action="extract_office.php" method="POST" enctype="multipart/form-data">
                    <div class="drop-zone" onclick="document.getElementById('office-input').click()">
                        <i class="fa-solid fa-file-export"></i>
                        <p>Prześlij plik Office</p>
                        <span style="font-size: 0.8rem; color: var(--text-dim);">Wybierz .docx lub .pptx</span>
                        <input type="file" name="office_file" id="office-input" accept=".docx,.pptx" style="display:none" onchange="updateLabel(this, 'office-label')" required>
                        <div id="office-label" class="file-info"></div>
                    </div>
                    <label style="font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.5rem; display: block; font-weight: 600;">JĘZYK DOCELOWY</label>
                    <select name="lang" class="form-select">
                        <option value="cs" selected>Czeski</option>
                        <option value="de">Niemiecki</option>
                        <option value="sk">Słowacki</option>
                        <option value="hu">Węgierski</option>
                        <option value="es">Hiszpański</option>
                        <option value="fr">Francuski</option>
                        <option value="it">Włoski</option>
                        <option value="pl">Polski</option>
                    </select>
                    <button type="submit" class="btn-submit">Wyodrębnij z Office</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function updateLabel(input, labelId) {
            const label = document.getElementById(labelId);
            if (input.files.length) {
                label.textContent = "Wybrano: " + input.files[0].name;
                label.style.display = 'block';
            }
        }
    </script>
</body>
</html>