<?php // config.php

// ✅ BEZPIECZNE ŁADOWANIE KLUCZA
$secretConfig = [];
$secretFile = __DIR__ . '/secret_config.php';

if (file_exists($secretFile)) {
    $secretConfig = require $secretFile;
} else {
    // Fallback dla developera - UTWÓRZ PLIK secret_config.php!
    die("Brak pliku secret_config.php! Utwórz go na podstawie secret_config.example.php");
}

return [
    // ✅ BEZPIECZNY KLUCZ API - z osobnego pliku
    'openai_key' => $secretConfig['openai_key'] ?? '',
    'paypal_client_id' => $secretConfig['paypal_client_id'] ?? '',
    'paypal_secret' => $secretConfig['paypal_secret'] ?? '',
    'paypal_mode' => $secretConfig['paypal_mode'] ?? 'sandbox',
    
    'default_lang' => 'cs',
    'upload_dir' => __DIR__ . '/uploads/',
    'workspace_dir' => __DIR__ . '/workspace/',
    // Legacy dirs — kept for cron_cleanup backward compat
    'temp_dir' => __DIR__ . '/temp/',
    'output_dir' => __DIR__ . '/output/',
    'csv_dir' => __DIR__ . '/translations/',
    
    // Social Auth Config
    'social' => [
        'google' => [
            'client_id'     => $secretConfig['google_client_id'] ?? '',
            'client_secret' => $secretConfig['google_client_secret'] ?? '',
            'redirect_uri'  => 'https://indd-translation.com/social_auth.php?provider=google'
        ]
    ],
	
    'skip_tokens' => ['COB', 'PAR', 'BAR', ':', '-', '–', '—', '•', '*', '...', '(', ')', '	...' ],
	
    'preserveMap' => [
        'Auto' => 'Auto',
        'Slave' => 'Slave',
        'ON' => 'ON',
        'OFF' => 'OFF',
        'Tilt Fine' => 'Tilt Fine',
        'SL1' => 'SL1',
		'Focus' => 'Focus',
    ],
];