<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W budowie - Translate.pro</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-body);
            overflow: hidden;
        }
        .construction-card {
            text-align: center;
            max-width: 500px;
            padding: 4rem;
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: var(--accent);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 10px 30px rgba(0, 113, 227, 0.3);
            color: white;
            font-size: 2.5rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 1rem;
            color: var(--text-main);
        }
        p {
            color: var(--text-dim);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }
        .loader-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        .dot {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            animation: dotPulse 1.5s infinite ease-in-out;
        }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.5); opacity: 1; }
        }
        
        .admin-link {
            position: fixed;
            bottom: 2rem;
            color: var(--border);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s;
        }
        .admin-link:hover { color: var(--text-dim); }
    </style>
</head>
<body>

    <div class="glass-card construction-card">
        <div class="icon-box">
            <i class="fas fa-hammer"></i>
        </div>
        <h1>Pracujemy nad czymś wielkim</h1>
        <p>Aktualnie dopieszczamy ostatnie detale naszej nowej platformy tłumaczeniowej. Zapraszamy już wkrótce!</p>
        
        <div class="loader-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <a href="login.php" class="admin-link">Zaloguj się (Admin)</a>

</body>
</html>
