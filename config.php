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
    
    'default_lang' => 'cs',
    'upload_dir' => __DIR__ . '/uploads/',
    'temp_dir' => __DIR__ . '/temp/',
    'output_dir' => __DIR__ . '/output/',
    'csv_dir' => __DIR__ . '/translations/',
	
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