<?php

/**
 * Translation Helper Functions
 * Add these to app/Helpers/Helpers.php or create new helper file
 */

if (!function_exists('trans_key')) {
    /**
     * Get translation for a key
     * 
     * @param string $key
     * @param string $language
     * @return string
     */
    function trans_key($key, $language = null) {
        if (is_null($language)) {
            $language = session('language', 'en');
        }
        
        $translations = config('translations');
        
        if (isset($translations[$language][$key])) {
            return $translations[$language][$key];
        }
        
        // Fallback to English
        if (isset($translations['en'][$key])) {
            return $translations['en'][$key];
        }
        
        return $key;
    }
}

if (!function_exists('get_current_language')) {
    /**
     * Get current language from session
     * 
     * @return string
     */
    function get_current_language() {
        return session('language', 'en');
    }
}

if (!function_exists('get_current_theme')) {
    /**
     * Get current theme from session
     * 
     * @return string
     */
    function get_current_theme() {
        return session('theme', 'light');
    }
}

if (!function_exists('set_language')) {
    /**
     * Set language in session
     * 
     * @param string $language
     */
    function set_language($language) {
        session(['language' => $language]);
    }
}

if (!function_exists('set_theme')) {
    /**
     * Set theme in session
     * 
     * @param string $theme
     */
    function set_theme($theme) {
        session(['theme' => $theme]);
    }
}
