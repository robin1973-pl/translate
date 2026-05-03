<?php

function get_user_language($available_languages = ['pl', 'en', 'de', 'cs']) {
    if (isset($_GET['lang']) && in_array($_GET['lang'], $available_languages)) {
        $_SESSION['ui_lang'] = $_GET['lang'];
        return $_GET['lang'];
    }

    if (isset($_SESSION['ui_lang']) && in_array($_SESSION['ui_lang'], $available_languages)) {
        return $_SESSION['ui_lang'];
    }

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($langs as $lang) {
            $lang_code = strtolower(substr($lang, 0, 2));
            if (in_array($lang_code, $available_languages)) {
                return $lang_code;
            }
        }
    }

    return 'en'; // Default fallback
}
