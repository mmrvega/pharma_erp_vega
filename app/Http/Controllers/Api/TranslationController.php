<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Get translations for a specific language
     * 
     * @param string $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($language = 'en')
    {
        // Validate language
        $validLanguages = ['en', 'ar'];
        if (!in_array($language, $validLanguages)) {
            $language = 'en';
        }

        // Get translations from config
        $translations = config('translations');
        
        if (isset($translations[$language])) {
            return response()->json($translations[$language]);
        }

        // Fallback to English
        return response()->json($translations['en']);
    }

    /**
     * Set language preference
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLanguage(Request $request)
    {
        $language = $request->input('language', 'en');
        
        // Validate language
        $validLanguages = ['en', 'ar'];
        if (!in_array($language, $validLanguages)) {
            return response()->json(['error' => 'Invalid language'], 400);
        }

        // Store in session
        session(['language' => $language]);
        
        return response()->json([
            'success' => true,
            'language' => $language
        ]);
    }

    /**
     * Set theme preference
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setTheme(Request $request)
    {
        $theme = $request->input('theme', 'light');
        
        // Validate theme
        $validThemes = ['light', 'dark'];
        if (!in_array($theme, $validThemes)) {
            return response()->json(['error' => 'Invalid theme'], 400);
        }

        // Store in session
        session(['theme' => $theme]);
        
        return response()->json([
            'success' => true,
            'theme' => $theme
        ]);
    }
}
