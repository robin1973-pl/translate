<?php
include 'auth.php';
$db = new SQLITE3('users.db');

// Pobieranie danych użytkownika
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $_SESSION['user_id']);
$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

$credits = $user['credits'] ?? 0;
$plan_name = ($user['role'] == 'admin') ? 'Administrator' : 'Standard Plan';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Translate.pro</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Dodatkowe szlify specyficzne dla Dashboardu */
        .stat-card {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--accent);
        }
        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background: rgba(0, 113, 227, 0.02);
        }
        .drop-zone:hover {
            border-color: var(--accent);
            background: rgba(0, 113, 227, 0.05);
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation (Apple Style) -->
    <nav class="nav-sidebar">
        <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 12px; padding: 0 10px;">
            <div style="width: 32px; height: 32px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-bolt" style="color: white !important;"></i>
            </div>
            <span style="font-weight: 800; font-size: 1.2rem; letter-spacing: -0.5px;">Translate.pro</span>
        </div>

        <a href="dashboard.php" class="nav-link active">
            <i class="fas fa-house"></i> <span>Panel główny</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-folder-open"></i> <span>Moje pliki</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-credit-card"></i> <span>Płatności</span>
        </a>
        
        <?php if ($user['role'] == 'admin'): ?>
        <div style="margin: 2rem 0 0.5rem 1rem; font-size: 0.7rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Administracja</div>
        <a href="admin_panel.php" class="nav-link">
            <i class="fas fa-user-shield"></i> <span>Zarządzanie</span>
        </a>
        <?php endif; ?>

        <div style="margin-top: auto;">
            <div class="glass-card" style="padding: 1rem; border-radius: 16px; margin-bottom: 1rem; border: none; background: var(--border);">
                <div style="font-size: 0.75rem; color: var(--text-dim); margin-bottom: 4px;">Zalogowany jako:</div>
                <div style="font-weight: 600; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($user['email']) ?>
                </div>
            </div>
            <a href="logout.php" class="nav-link" style="color: #ff3b30;">
                <i class="fas fa-right-from-bracket" style="color: #ff3b30 !important;"></i> <span>Wyloguj się</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Header Section -->
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <h1 id="dynamicGreeting" style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 8px; opacity: 0; transform: translateY(10px); transition: all 0.8s ease;">Witaj, Robert</h1>
                <p style="color: var(--text-dim); font-size: 1.1rem;">Twoje centrum inteligentnych tłumaczeń dokumentów.</p>
            </div>
            <div class="glass-card" style="padding: 1rem 2rem; border-radius: 100px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-dim);">Dostępne środki:</span>
                <span style="font-size: 1.2rem; font-weight: 800; color: var(--accent);"><?= $credits ?> kredytów</span>
            </div>
        </header>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const greetings = {
                    'pl': 'Witaj',
                    'en': 'Welcome',
                    'de': 'Willkommen',
                    'fr': 'Bienvenue',
                    'es': 'Bienvenido',
                    'it': 'Benvenuto'
                };
                
                const userLang = navigator.language.substring(0, 2).toLowerCase();
                const greetingWord = greetings[userLang] || greetings['en'];
                const userName = 'Robert'; // Możemy to później pobierać z bazy
                
                const greetingEl = document.getElementById('dynamicGreeting');
                greetingEl.innerText = `${greetingWord}, ${userName}`;
                
                // Płynne pojawienie się
                setTimeout(() => {
                    greetingEl.style.opacity = '1';
                    greetingEl.style.transform = 'translateY(0)';
                }, 100);
            });
        </script>

        <!-- Action Cards Grid -->
        <div class="dashboard-grid">
            
            <!-- Upload Card -->
            <div class="glass-card" style="grid-column: span 2;">
                <div class="section-title">
                    <i class="fas fa-file-export" style="color: var(--accent);"></i> Prześlij dokument
                </div>
                <form action="process_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <label class="drop-zone" for="fileInput">
                        <i class="fas fa-cloud-arrow-up" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem; display: block;"></i>
                        <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px;">Przeciągnij plik tutaj</div>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">Obsługiwane formaty: <b>IDML, DOCX, PPTX</b></p>
                        <input type="file" id="fileInput" name="file" style="display: none;" onchange="this.form.submit()">
                    </label>
                </form>
            </div>

            <!-- Recharge Card -->
            <div class="glass-card">
                <div class="section-title">
                    <i class="fas fa-wallet" style="color: var(--accent);"></i> Doładuj konto
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="payment_paypal.php?amount=50" class="apple-btn" style="justify-content: center;">
                        <i class="fa-brands fa-paypal"></i> 10 kredytów (50 zł)
                    </a>
                    <a href="payment_paypal.php?amount=200" class="apple-btn" style="justify-content: center;">
                        <i class="fa-brands fa-paypal"></i> 50 kredytów (200 zł)
                    </a>
                </div>
                <p style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-dim); text-align: center;">
                    Płatności obsługiwane przez PayPal (BLIK, P24, Karty).
                </p>
            </div>
        </div>

        <!-- Translation History -->
        <div style="margin-top: 3rem;">
            <div class="section-title">
                <i class="fas fa-clock-rotate-left" style="color: var(--accent);"></i> Ostatnie tłumaczenia
            </div>
            
            <?php
            $stmt_jobs = $db->prepare("SELECT * FROM jobs WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
            $stmt_jobs->bindValue(':uid', $_SESSION['user_id']);
            $jobs = $stmt_jobs->execute();
            $hasJobs = false;
            while ($job = $jobs->fetchArray(SQLITE3_ASSOC)):
                $hasJobs = true;
                $statusClass = ($job['status'] == 'completed') ? 'badge-success' : 'badge-warning';
                $statusText = ($job['status'] == 'completed') ? 'Gotowe' : 'W kolejce';
            ?>
            <div class="list-item">
                <div style="width: 44px; height: 44px; background: var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
                    <i class="fas fa-file-lines" style="color: var(--text-main);"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);"><?= htmlspecialchars($job['filename']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-dim);"><?= date('d.m.Y, H:i', strtotime($job['created_at'])) ?></div>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                    <?php if ($job['status'] == 'completed'): ?>
                        <a href="download.php?id=<?= $job['id'] ?>" class="apple-btn apple-btn-secondary" style="padding: 8px 16px; font-size: 0.8rem;">
                            <i class="fas fa-download"></i> Pobierz
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if (!$hasJobs): ?>
            <div class="glass-card" style="text-align: center; padding: 3rem; border-style: dashed; background: transparent;">
                <p style="color: var(--text-dim);">Nie masz jeszcze żadnych tłumaczeń. Prześlij swój pierwszy plik!</p>
            </div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        // Automatyczne odświeżanie statusu w kolejce
        <?php if ($hasJobs): ?>
        setInterval(() => {
            fetch('api_check_status.php')
                .then(r => r.json())
                .then(data => {
                    if (data.reload) window.location.reload();
                });
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>