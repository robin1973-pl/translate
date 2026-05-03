<?php require_once 'helpers/ui_strings.php'; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Polityka Prywatności - INDD Translation</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }
        .container { max-width: 800px; margin: 4rem auto; padding: 2rem; background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 24px; backdrop-filter: blur(20px); }
        h1 { font-weight: 800; letter-spacing: -1px; margin-bottom: 2rem; }
        h2 { margin-top: 2rem; color: var(--accent); }
        p { margin-bottom: 1rem; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" style="color: var(--accent); text-decoration: none; font-weight: 600; display: block; margin-bottom: 2rem;">
            <i class="fas fa-arrow-left"></i> Powrót
        </a>
        <h1>Polityka Prywatności</h1>
        <p>Ostatnia aktualizacja: <?= date('d.m.Y') ?></p>

        <h2>1. Informacje Ogólne</h2>
        <p>Niniejsza Polityka Prywatności określa zasady przetwarzania i ochrony danych osobowych użytkowników serwisu INDD Translation (indd-translation.com). Szanujemy Twoją prywatność i dbamy o bezpieczeństwo Twoich danych.</p>

        <h2>2. Jakie dane zbieramy?</h2>
        <p>W ramach korzystania z serwisu możemy zbierać:</p>
        <ul>
            <li>Adres e-mail oraz nazwę użytkownika (przy logowaniu przez Google).</li>
            <li>Dane zawarte w przesyłanych plikach IDML/DOCX (wyłącznie w celu ich przetłumaczenia).</li>
            <li>Informacje o płatnościach (obsługiwane przez PayPal, nie przechowujemy danych kart płatniczych).</li>
        </ul>

        <h2>3. Cel przetwarzania danych</h2>
        <p>Twoje dane są przetwarzane wyłącznie w celu:</p>
        <ul>
            <li>Świadczenia usługi automatycznego tłumaczenia dokumentów.</li>
            <li>Umożliwienia logowania i personalizacji konta.</li>
            <li>Zapewnienia bezpieczeństwa i wsparcia technicznego.</li>
        </ul>

        <h2>4. Przechowywanie plików</h2>
        <p>Pliki przesyłane do serwisu są automatycznie usuwane z naszych serwerów po 24 godzinach od momentu ich przetłumaczenia.</p>

        <h2>5. Twoje prawa</h2>
        <p>Masz prawo do wglądu w swoje dane, ich poprawiania oraz żądania ich usunięcia (zgodnie z RODO). W celu usunięcia konta prosimy o kontakt lub skorzystanie z zakładki "Usuwanie danych".</p>

        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--glass-border); font-size: 0.9rem; color: var(--text-dim);">
            INDD Translation &copy; <?= date('Y') ?> | Kontakt: support@indd-translation.com
        </div>
    </div>
</body>
</html>
