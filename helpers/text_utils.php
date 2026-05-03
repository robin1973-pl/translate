<?php
// helpers/text_utils.php

if (!function_exists('protectSpacesForTranslation')) {
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
}

if (!function_exists('restoreSpacesAfterTranslation')) {
    function restoreSpacesAfterTranslation(string $text): string {
        $text = preg_replace_callback('/\{__LEADSPACES__(\d+)__\}/u', function($m){
            return str_repeat(' ', (int)$m[1]);
        }, $text);

        $text = preg_replace_callback('/\{__TRAILSPACES__(\d+)__\}/u', function($m){
            return str_repeat(' ', (int)$m[1]);
        }, $text);

        return $text;
    }
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? rrmdir($file) : unlink($file);
    }
}
