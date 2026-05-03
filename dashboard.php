<?php
date_default_timezone_set('Europe/Warsaw');
include 'auth.php';
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$db = new SQLITE3('users.db');

// Pobieranie danych użytkownika
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $_SESSION['user_id']);
$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

$credits = $user['credits'] ?? 0;
$d_ui = $strings[$ui_lang]['dashboard'];
$plan_name = ($user['role'] == 'admin') ? $d_ui['admin_role'] : $d_ui['plan_standard'];
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $strings[$ui_lang]['nav']['dashboard'] ?> - indd-translation.com</title>
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
            border: 2px dashed rgba(0, 113, 227, 0.2);
            border-radius: 24px;
            padding: 5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            background: rgba(255, 255, 255, 0.01);
            position: relative;
            overflow: hidden;
        }
        .drop-zone:hover {
            border-color: var(--accent);
            background: rgba(0, 113, 227, 0.03);
            transform: scale(1.01);
        }
        .drop-zone.dragover {
            border-color: var(--accent);
            background: rgba(0, 113, 227, 0.08);
            box-shadow: 0 0 30px rgba(0, 113, 227, 0.1);
        }
        .upload-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), #22d3ee);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(8, 145, 178, 0.3);
            transition: var(--transition);
        }
        .drop-zone:hover .upload-icon-wrapper {
            transform: translateY(-5px) rotate(5deg);
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
        .apple-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .apple-btn:hover {
            transform: translateY(-2px);
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        .apple-btn-secondary {
            background: var(--bg-sidebar);
        }
    </style>
</head>
<body class="<?= $_SESSION['theme'] ?? '' ?>">

    <!-- Sidebar Navigation (Apple Style) -->
    <nav class="nav-sidebar">
        <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 12px; padding: 0 10px;">
            <div style="width: 32px; height: 32px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-pen-nib" style="color: white !important;"></i>
            </div>
            <span style="font-weight: 800; font-size: 1.2rem; letter-spacing: -0.5px;">INDD Translation</span>
        </div>

        <a href="dashboard.php" class="nav-link active">
            <i class="fas fa-house"></i> <span><?= $d_ui['sidebar_main'] ?></span>
        </a>
        <a href="dashboard.php#history" class="nav-link">
            <i class="fas fa-cloud-download-alt"></i> <span><?= $d_ui['sidebar_files'] ?></span>
        </a>
        <a href="dashboard.php#recharge" class="nav-link">
            <i class="fas fa-credit-card"></i> <span><?= $d_ui['sidebar_payments'] ?></span>
        </a>
        <a href="help.php" class="nav-link">
            <i class="fas fa-circle-question"></i> <span><?= $strings[$ui_lang]['help']['title'] ?></span>
        </a>
        
        <div style="padding: 15px 20px; margin-top: 10px; display: flex; gap: 12px; border-top: 1px solid var(--glass-border);">
            <a href="?lang=pl" title="Polski" style="text-decoration: none; font-size: 1.1rem;">🇵🇱</a>
            <a href="?lang=en" title="English" style="text-decoration: none; font-size: 1.1rem;">🇬🇧</a>
            <a href="?lang=de" title="Deutsch" style="text-decoration: none; font-size: 1.1rem;">🇩🇪</a>
            <a href="?lang=cs" title="Čeština" style="text-decoration: none; font-size: 1.1rem;">🇨🇿</a>
        </div>
        
        <?php if ($user['role'] == 'admin'): ?>
        <div style="margin: 2rem 0 0.5rem 1rem; font-size: 0.7rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;"><?= $d_ui['sidebar_admin'] ?></div>
        <a href="admin_panel.php" class="nav-link">
            <i class="fas fa-user-shield"></i> <span><?= $d_ui['sidebar_admin'] ?></span>
        </a>
        <?php endif; ?>

        <div style="margin-top: auto;">
            <div class="glass-card" style="padding: 1rem; border-radius: 16px; margin-bottom: 1rem; border: none; background: var(--border); display: flex; align-items: center; gap: 12px;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                <?php else: ?>
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--glass-bg); display: flex; align-items: center; justify-content: center; border: 1px solid var(--glass-border);">
                        <i class="fas fa-user" style="font-size: 0.8rem; color: var(--text-dim);"></i>
                    </div>
                <?php endif; ?>
                <div style="flex: 1; overflow: hidden;">
                    <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;"><?= $d_ui['sidebar_logged_in'] ?></div>
                    <div style="font-weight: 600; font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($user['username']) ?>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="nav-link" style="color: #ff3b30;">
                <i class="fas fa-right-from-bracket" style="color: #ff3b30 !important;"></i> <span><?= $d_ui['sidebar_logout'] ?></span>
            </a>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        
        <!-- Header Section -->
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <h1 id="dynamicGreeting" style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 8px; opacity: 0; transform: translateY(10px); transition: all 0.8s ease;"><?= $d_ui['greeting'] ?>, <?= htmlspecialchars($user['username']) ?></h1>
                <p style="color: var(--text-dim); font-size: 1.1rem;"><?= $d_ui['welcome_subtitle'] ?></p>
            </div>
            <div class="glass-card" style="padding: 1rem 2rem; border-radius: 100px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-dim);"><?= $d_ui['credits_label'] ?>:</span>
                <span style="font-size: 1.2rem; font-weight: 800; color: var(--accent);"><?= $credits ?> <?= $d_ui['credits_suffix'] ?></span>
            </div>
        </header>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const uiLang = '<?= $ui_lang ?>';
                const greetingWord = '<?= addslashes($d_ui['greeting']) ?>';
                const userName = '<?= addslashes($user['username']) ?>'; 
                
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
            <div class="glass-card" style="margin-bottom: 2rem;">
                <form id="uploadForm" action="process_upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="file" id="fileInput" style="display: none;" accept=".idml,.docx,.pptx">
                    <input type="hidden" name="lang" id="targetLangInput" value="en">
                    
                    <div class="drop-zone" id="dropZone">
                        <div class="upload-icon-wrapper">
                            <i class="fas fa-cloud-arrow-up"></i>
                        </div>
                        <h2 style="font-weight: 800; letter-spacing: -0.5px; margin-bottom: 0.5rem;"><?= $d_ui['upload_title'] ?></h2>
                        <p style="color: var(--text-dim);"><?= $d_ui['upload_hint'] ?></p>
                        
                        <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 10px;">
                            <span style="padding: 4px 12px; background: rgba(0,0,0,0.05); border-radius: 6px; font-size: 0.7rem; font-weight: 700; color: var(--text-dim);">IDML</span>
                            <span style="padding: 4px 12px; background: rgba(0,0,0,0.05); border-radius: 6px; font-size: 0.7rem; font-weight: 700; color: var(--text-dim);">DOCX</span>
                            <span style="padding: 4px 12px; background: rgba(0,0,0,0.05); border-radius: 6px; font-size: 0.7rem; font-weight: 700; color: var(--text-dim);">PPTX</span>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                const dropZone = document.getElementById('dropZone');
                const fileInput = document.getElementById('fileInput');
                const uploadForm = document.getElementById('uploadForm');

                dropZone.addEventListener('click', () => fileInput.click());

                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });

                ['dragleave', 'dragend'].forEach(type => {
                    dropZone.addEventListener(type, () => dropZone.classList.remove('dragover'));
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        handleFileSubmit();
                    }
                });

                fileInput.addEventListener('change', handleFileSubmit);

                function handleFileSubmit() {
                    console.log("File selected!");
                    const file = fileInput.files[0];
                    if (!file) {
                        console.log("No file found.");
                        return;
                    }

                    const ext = file.name.split('.').pop().toLowerCase();
                    console.log("File extension:", ext);
                    const overlay = document.getElementById('processingOverlay');
                    
                    if (ext === 'idml' || ext === 'docx' || ext === 'pptx') {
                        uploadForm.action = 'process_upload.php';
                        fileInput.name = 'file';
                    } else {
                        alert('<?= addslashes($d_ui['error_unsupported']) ?>');
                        return;
                    }
                    
                    console.log("Showing overlay and submitting form to", uploadForm.action);
                    overlay.style.display = 'flex';
                    uploadForm.submit();
                }
                </script>

            <!-- Recharge Card -->
            <div class="glass-card" id="recharge">
                <div class="section-title">
                    <i class="fas fa-wallet" style="color: var(--accent);"></i> <?= $d_ui['recharge_title'] ?>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="payment_paypal.php?amount=50" class="apple-btn" style="justify-content: center;">
                        <i class="fa-brands fa-paypal"></i> 10 <?= $d_ui['credits_suffix'] ?> (50 PLN)
                    </a>
                    <a href="payment_paypal.php?amount=200" class="apple-btn" style="justify-content: center;">
                        <i class="fa-brands fa-paypal"></i> 50 <?= $d_ui['credits_suffix'] ?> (200 PLN)
                    </a>
                </div>
                <p style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-dim); text-align: center;">
                    <?= $d_ui['recharge_desc'] ?>
                </p>
            </div>

            <!-- Payment History -->
            <div class="glass-card" style="margin-top: 2rem;">
                <div class="section-title">
                    <i class="fas fa-receipt" style="color: var(--accent);"></i> <?= $d_ui['history_payments'] ?>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="text-align: left; color: var(--text-dim); border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 12px;"><?= $d_ui['table_date'] ?></th>
                                <th style="padding: 12px;"><?= $d_ui['table_amount'] ?></th>
                                <th style="padding: 12px;"><?= $d_ui['table_details'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt_pay = $db->prepare("SELECT * FROM credit_logs WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
                            $stmt_pay->bindValue(':uid', $_SESSION['user_id']);
                            $pay_res = $stmt_pay->execute();
                            $hasPay = false;
                            while ($pay = $pay_res->fetchArray(SQLITE3_ASSOC)):
                                $hasPay = true;
                            ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 12px;"><?= date('d.m.Y', strtotime($pay['created_at'])) ?></td>
                                <td style="padding: 12px; font-weight: 700; color: var(--accent);">+<?= $pay['amount'] ?> <?= $d_ui['credits_suffix'] ?></td>
                                <td style="padding: 12px; color: var(--text-dim); font-size: 0.8rem;"><?= htmlspecialchars($pay['reason']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasPay): ?>
                            <tr>
                                <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-dim);"><?= $d_ui['no_history'] ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Translation History -->
        <div style="margin-top: 3rem;" id="history">
            <div class="section-title">
                <i class="fas fa-clock-rotate-left" style="color: var(--accent);"></i> <?= $d_ui['history_translations'] ?>
            </div>
            
            <?php
            $stmt_jobs = $db->prepare("SELECT * FROM jobs WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
            $stmt_jobs->bindValue(':uid', $_SESSION['user_id']);
            $jobs = $stmt_jobs->execute();
            $hasJobs = false;
            while ($job = $jobs->fetchArray(SQLITE3_ASSOC)):
                $hasJobs = true;
                $statusClass = '';
                $statusText = '';
                $canContinue = false;
                
                if ($job['status'] == 'completed') {
                    $statusClass = 'badge-success';
                    $statusText = $d_ui['status_ready'];
                } elseif ($job['status'] == 'processing' || $job['status'] == 'uploaded') {
                    $statusClass = 'badge-warning';
                    $statusText = $d_ui['status_wait'];
                    $canContinue = true;
                } else {
                    $statusClass = 'badge-warning';
                    $statusText = $d_ui['status_queue'];
                }
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
                            <i class="fas fa-download"></i> <?= $d_ui['btn_download'] ?>
                        </a>
                    <?php elseif ($canContinue): ?>
                        <a href="process_resume.php?id=<?= $job['id'] ?>" class="apple-btn" style="padding: 8px 16px; font-size: 0.8rem; background: var(--accent); color: white;">
                            <i class="fas fa-play"></i> <?= $d_ui['btn_continue'] ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if (!$hasJobs): ?>
            <div class="glass-card" style="text-align: center; padding: 3rem; border-style: dashed; background: transparent;">
                <p style="color: var(--text-dim);"><?= $d_ui['no_translations'] ?></p>
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

    <!-- Processing Overlay at bottom for better reliability -->
    <div id="processingOverlay">
        <div class="spinner-container">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-icon">
                <i class="fas fa-pen-nib"></i>
            </div>
        </div>
        <div class="processing-text"><?= $d_ui['processing_title'] ?></div>
        <div class="processing-sub"><?= $d_ui['processing_sub'] ?></div>
    </div>

    <script>
        window.onerror = function(msg, url, line) {
            console.error("Global JS Error:", msg, "at", url, ":", line);
            return false;
        };
    </script>
</body>
</html>