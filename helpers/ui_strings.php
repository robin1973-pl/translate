<?php
// helpers/ui_strings.php – Słownik wielojęzyczny dla interfejsu (I18n)

return [
    'pl' => [
        'index' => [
            'title' => 'IDML Translator',
            'welcome_title' => 'IDML Translator',
            'file_label' => 'Wybierz plik .idml (ZIP)',
            'lang_label' => 'Język docelowy',
            'submit_btn' => 'Wyodrębnij teksty do tłumaczenia',
        ],
        'translate_ui' => [
            'title' => 'Podgląd tłumaczenia',
            'header_prefix' => 'Tłumaczenie na język:',
            'table' => [
                'file' => 'Plik',
                'index' => '#',
                'original' => 'Oryginał',
                'translation' => 'Tłumaczenie',
            ],
            'buttons' => [
                'download_idml' => '📦 Pobierz IDML',
                'auto_translate' => 'Tłumacz wszystko automatycznie',
                'back' => '← Powrót do startu',
            ],
            'loader' => [
                'title' => 'Automatyczne tłumaczenie',
                'status_preparing' => 'Przygotowywanie zadań...',
                'error_prefix' => '! Błąd:',
                'log_prefix' => '> Tłumaczenie:',
            ],
            'messages' => [
                'no_data' => 'Brak danych do tłumaczenia.',
                'too_long_title' => 'To tłumaczenie może być zbyt długie i wymagać korekty.',
                'finish_alert' => 'Tłumaczenie zakończone na język {lang}. Pomyślnie: {success}, Błędy: {error}',
            ]
        ]
    ],
    // ✅ NOWA WERSJA ANGIELSKA NA PRÓBĘ (Możesz tu dodawać kolejne języki)
    'en' => [
        'index' => [
            'title' => 'IDML Translator',
            'welcome_title' => 'IDML Translator',
            'file_label' => 'Choose .idml file (ZIP)',
            'lang_label' => 'Target language',
            'submit_btn' => 'Extract content for translation',
        ],
        'translate_ui' => [
            'title' => 'Translation Preview',
            'header_prefix' => 'Translation to:',
            'table' => [
                'file' => 'File',
                'index' => '#',
                'original' => 'Original',
                'translation' => 'Translation',
            ],
            'buttons' => [
                'download_idml' => '📦 Download IDML',
                'auto_translate' => 'Auto-translate everything',
                'back' => '← Back to Start',
            ],
            'loader' => [
                'title' => 'Auto-translation',
                'status_preparing' => 'Preparing tasks...',
                'error_prefix' => '! Error:',
                'log_prefix' => '> Translating:',
            ],
            'messages' => [
                'no_data' => 'No data for translation.',
                'too_long_title' => 'This translation might be too long and require adjustment.',
                'finish_alert' => 'Translation finished for {lang}. Successful: {success}, Errors: {error}',
            ]
        ]
    ]
];
