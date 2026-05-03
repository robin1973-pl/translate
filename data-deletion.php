<?php require_once 'helpers/ui_strings.php'; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Usuwanie Danych - INDD Translation</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }
        .container { max-width: 800px; margin: 4rem auto; padding: 3rem; background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 24px; backdrop-filter: blur(20px); text-align: center; }
        h1 { font-weight: 800; letter-spacing: -1px; margin-bottom: 1.5rem; }
        .info-box { background: rgba(0,0,0,0.05); padding: 2rem; border-radius: 16px; margin-top: 2rem; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-user-slash" style="font-size: 3rem; color: var(--accent); margin-bottom: 2rem;"></i>
        <h1>Instrukcja usuwania konta i danych</h1>
        <p>Szanujemy Twoją prywatność. Jeśli chcesz przestać korzystać z serwisu INDD Translation, masz pełne prawo do usunięcia wszystkich swoich danych.</p>

        <div class="info-box">
            <h3 style="margin-top: 0;">Jak usunąć swoje konto?</h3>
            <p>Aby trwale usunąć swoje konto z naszego serwisu oraz wszystkie powiązane z nim informacje:</p>
            <ol>
                <li>Zaloguj się do swojego profilu.</li>
                <li>Wyślij wiadomość e-mail na adres: <a href="mailto:support@indd-translation.com" style="color: var(--accent);">support@indd-translation.com</a>.</li>
                <li>W temacie wpisz: <strong>"Usunięcie konta - [Twój adres e-mail]"</strong>.</li>
            </ol>
            <p style="margin-top: 1.5rem;">Twoje konto oraz wszystkie dane zostaną trwale usunięte z naszej bazy w ciągu 48 godzin od otrzymania zgłoszenia. Pamiętaj, że jest to proces nieodwracalny.</p>
        </div>

        <div style="margin-top: 3rem;">
            <a href="index.php" class="apple-btn" style="display: inline-flex; justify-content: center;">Wróć do strony głównej</a>
        </div>
    </div>
</body>
</html>
