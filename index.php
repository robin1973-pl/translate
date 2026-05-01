<?php
include 'auth.php'; 
$config = require 'config.php';
$strings = require 'helpers/ui_strings.php';

// Możesz tu dodać wybór języka w przyszłości, teraz domyślnie PL
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
            --accent-glow: rgba(6, 182, 212, 0.2);
            --glass: rgba(15, 23, 42, 0.6);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #d8e3fb;
            --text-dim: #869397;
        }
        body { 
            background: #081425;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        /* Dashboard Layout */
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
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -1px;
            color: white;
            text-decoration: none;
        }
        .logo span { color: var(--accent); }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .user-nav span { font-size: 0.9rem; font-weight: 500; }
        .btn-logout {
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }
        .btn-logout:hover { color: #f87171; }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 4rem 2rem;
        }

        .upload-container {
            width: 100%;
            max-width: 600px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .drop-zone {
            border: 2px dashed var(--glass-border);
            border-radius: 16px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: var(--accent);
            background: rgba(6, 182, 212, 0.05);
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .drop-zone i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
            display: block;
        }
        .drop-zone p { margin: 0; font-weight: 500; }
        .drop-zone span { font-size: 0.85rem; color: var(--text-dim); margin-top: 0.5rem; display: block; }
        
        #file-input { display: none; }

        .form-select {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            margin-bottom: 2rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
        }
        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.4);
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -5px rgba(6, 182, 212, 0.6);
        }
        .btn-submit:active { transform: translateY(0); }

        .file-info {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--accent);
            font-weight: 600;
            display: none;
        }

        /* Ambient background glow */
        .ambient-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.1) 0%, transparent 70%);
            z-index: -1;
            pointer-events: none;
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
        <div class="upload-container">
            <h2 style="margin-top: 0; font-size: 1.5rem; letter-spacing: -0.5px;"><?= $ui['upload_title'] ?></h2>
            
            <form action="extract_idml.php" method="POST" enctype="multipart/form-data" id="upload-form">
                <div class="drop-zone" id="drop-zone">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <p><?= $ui['upload_hint'] ?></p>
                    <span>Obsługiwane formaty: .idml, .zip</span>
                    <input type="file" name="idml" id="file-input" accept=".idml,.zip" required>
                    <div id="file-name" class="file-info"></div>
                </div>

                <div style="text-align: left; margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600; letter-spacing: 1px;"><?= strtoupper($ui['target_lang']) ?></label>
                </div>
                <select name="lang" id="lang" class="form-select">
                    <option value="cs" selected>Czeski (Czech)</option>
                    <option value="de">Niemiecki (German)</option>
                    <option value="sk">Słowacki (Slovak)</option>
                    <option value="hu">Węgierski (Hungarian)</option>
                    <option value="es">Hiszpański (Spanish)</option>
                    <option value="fr">Francuski (French)</option>
                    <option value="it">Włoski (Italian)</option>
                    <option value="pt">Portugalski (Portuguese)</option>
                    <option value="nl">Holenderski (Dutch)</option>
                    <option value="pl">Polski (Polish)</option>
                    <option value="en">Angielski (English)</option>
                </select>

                <button type="submit" class="btn-submit"><?= $ui['submit'] ?></button>
            </form>
        </div>
    </main>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        ['dragleave', 'drop'].forEach(event => {
            dropZone.addEventListener(event, () => dropZone.classList.remove('dragover'));
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileName();
            }
        });

        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length) {
                fileNameDisplay.textContent = "Wybrany plik: " + fileInput.files[0].name;
                fileNameDisplay.style.display = 'block';
            }
        }
    </script>
</body>
</html>