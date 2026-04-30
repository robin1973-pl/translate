<?php
// helpers/format_helpers.php – Funkcje pomocnicze do formatowania tekstu i obsługi spacji

/**
 * Zabezpiecz spacje przed wysłaniem do tłumacza (zamień wiodące i końcowe spacje na tokeny)
 */
function protectSpacesForTranslation(string $text): string {
    // Leading spaces
    $text = preg_replace_callback('/^(\s+)/u', function($m){
        return '{__LEADSPACES__' . strlen($m[1]) . '__}';
    }, $text);

    // Trailing spaces
    $text = preg_replace_callback('/(\s+)$/u', function($m){
        return '{__TRAILSPACES__' . strlen($m[1]) . '__}';
    }, $text);

    return $text;
}

/**
 * Przywróć spacje po tłumaczeniu (zamień tokeny na rzeczywiste spacje)
 */
function restoreSpacesAfterTranslation(string $text): string {
    $text = preg_replace_callback('/\{__LEADSPACES__(\d+)__\}/u', function($m){
        return str_repeat(' ', (int)$m[1]);
    }, $text);

    $text = preg_replace_callback('/\{__TRAILSPACES__(\d+)__\}/u', function($m){
        return str_repeat(' ', (int)$m[1]);
    }, $text);

    return $text;
}
