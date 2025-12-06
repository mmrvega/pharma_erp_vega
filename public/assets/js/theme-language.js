/**
 * Theme & Language Manager
 * Handles dark mode toggle and language switching
 */

class ThemeLanguageManager {
    constructor() {
        this.storagePrefix = 'pharmacy_pos_';
        this.themes = {
            light: 'light-mode',
            dark: 'dark-mode'
        };
        this.languages = ['en', 'ar'];
        this.currentTheme = this.getStoredTheme();
        this.currentLanguage = this.getStoredLanguage();
        
        this.init();
    }

    /**
     * Initialize theme and language
     */
    init() {
        this.applyTheme(this.currentTheme);
        this.applyLanguage(this.currentLanguage);
        this.createThemeToggleButton();
        this.createLanguageToggleButton();
        // If header buttons exist, bind to them as well
        this.bindHeaderButtons();
        this.setupEventListeners();
    }

    /**
     * Get stored theme from localStorage
     */
    getStoredTheme() {
        const stored = localStorage.getItem(this.storagePrefix + 'theme');
        if (stored) return stored;
        
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    /**
     * Get stored language from localStorage
     */
    getStoredLanguage() {
        const stored = localStorage.getItem(this.storagePrefix + 'language');
        if (stored) return stored;
        
        // Check document lang or default to English
        const docLang = document.documentElement.lang;
        return (docLang === 'ar') ? 'ar' : 'en';
    }

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        const body = document.body;
        
        // Remove existing theme classes
        body.classList.remove('light-mode', 'dark-mode');
        
        // Apply new theme
        if (theme === 'dark') {
            body.classList.add('dark-mode');
        } else {
            body.classList.add('light-mode');
        }
        
        this.currentTheme = theme;
        localStorage.setItem(this.storagePrefix + 'theme', theme);
        
        // Dispatch event
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
    }

    /**
     * Apply language to document
     */
    applyLanguage(language) {
        document.documentElement.lang = language;
        document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
        
        // Add/remove RTL class
        const body = document.body;
        if (language === 'ar') {
            body.classList.add('rtl');
            body.classList.remove('ltr');
        } else {
            body.classList.add('ltr');
            body.classList.remove('rtl');
        }
        
        this.currentLanguage = language;
        localStorage.setItem(this.storagePrefix + 'language', language);
        
        // Load and apply translations
        this.loadTranslations(language);
        
        // Dispatch event
        window.dispatchEvent(new CustomEvent('languageChanged', { detail: { language: language } }));
    }

    /**
     * Toggle between light and dark theme
     */
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        
        // Update button text
        this.updateThemeToggleButton();
    }

    /**
     * Toggle between English and Arabic
     */
    toggleLanguage() {
        const newLanguage = this.currentLanguage === 'en' ? 'ar' : 'en';
        this.applyLanguage(newLanguage);
        
        // Update button text
        this.updateLanguageToggleButton();
    }

    /**
     * Create theme toggle button
     */
    createThemeToggleButton() {
        // Check if button already exists
        if (document.getElementById('theme-toggle-btn')) return;
        
        const button = document.createElement('button');
        button.id = 'theme-toggle-btn';
        button.className = 'theme-toggle-btn';
        button.innerHTML = this.currentTheme === 'dark' 
            ? '<i class="fas fa-sun"></i>' 
            : '<i class="fas fa-moon"></i>';
        button.title = this.currentTheme === 'dark' 
            ? 'Switch to Light Mode' 
            : 'Switch to Dark Mode';
        
        button.addEventListener('click', () => this.toggleTheme());
        
        document.body.appendChild(button);
    }

    /**
     * Create language toggle button
     */
  /**  createLanguageToggleButton() {
        // Check if button already exists
        if (document.getElementById('language-toggle-btn')) return;
        
        const button = document.createElement('button');
        button.id = 'language-toggle-btn';
        button.className = 'language-toggle-btn';
        button.innerHTML = this.currentLanguage === 'en' 
            ? '<span>العربية</span>' 
            : '<span>English</span>';
        button.title = this.currentLanguage === 'en' 
            ? 'Switch to Arabic' 
            : 'Switch to English';
        
        button.addEventListener('click', () => this.toggleLanguage());
        
        document.body.appendChild(button);
    }
*/
    /**
     * Update theme toggle button
     */
    updateThemeToggleButton() {
        const button = document.getElementById('theme-toggle-btn');
        if (button) {
            button.innerHTML = this.currentTheme === 'dark' 
                ? '<i class="fas fa-sun"></i>' 
                : '<i class="fas fa-moon"></i>';
            button.title = this.currentTheme === 'dark' 
                ? 'Switch to Light Mode' 
                : 'Switch to Dark Mode';
        }
        // Update header button if present
        const headerBtn = document.getElementById('header-theme-toggle');
        if (headerBtn) {
            headerBtn.innerHTML = this.currentTheme === 'dark' ? '<i class="fas fa-sun"></i> <span data-i18n="light_mode">' + (this.currentTheme === 'dark' ? 'Light' : 'Light') + '</span>' : '<i class="fas fa-moon"></i> <span data-i18n="dark_mode">' + (this.currentTheme === 'dark' ? 'Dark' : 'Dark') + '</span>';
            headerBtn.setAttribute('aria-label', this.currentTheme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        }
    }

    /**
     * Update language toggle button
     */
    updateLanguageToggleButton() {
        const button = document.getElementById('language-toggle-btn');
        if (button) {
            button.innerHTML = this.currentLanguage === 'en' 
                ? '<span>العربية</span>' 
                : '<span>English</span>';
            button.title = this.currentLanguage === 'en' 
                ? 'Switch to Arabic' 
                : 'Switch to English';
        }
        // Update header language link if present
        const headerLang = document.getElementById('header-language-toggle');
        if (headerLang) {
            headerLang.textContent = this.currentLanguage === 'en' ? 'العربية' : 'English';
            headerLang.setAttribute('title', this.currentLanguage === 'en' ? 'Switch to Arabic' : 'Switch to English');
        }
    }

    /**
     * Bind header buttons if they exist (for placing toggles in header)
     */
    bindHeaderButtons() {
        const headerTheme = document.getElementById('header-theme-toggle');
        if (headerTheme) {
            headerTheme.addEventListener('click', () => this.toggleTheme());
            // set initial state
            headerTheme.innerHTML = this.currentTheme === 'dark' ? '<i class="fas fa-sun"></i> <span data-i18n="light_mode">' + (this.currentTheme === 'dark' ? 'Light' : 'Light') + '</span>' : '<i class="fas fa-moon"></i> <span data-i18n="dark_mode">' + (this.currentTheme === 'dark' ? 'Dark' : 'Dark') + '</span>';
        }

        const headerLang = document.getElementById('header-language-toggle');
        if (headerLang) {
            headerLang.addEventListener('click', () => this.toggleLanguage());
            headerLang.textContent = this.currentLanguage === 'en' ? 'العربية' : 'English';
        }
    }

    /**
     * Load translations from server
     */
    loadTranslations(language) {
        // Fetch translations from server
        fetch(`/api/translations/${language}`)
            .then(response => response.json())
            .then(data => {
                this.applyTranslations(data);
            })
            .catch(error => console.log('Translations loaded from local config'));
    }

    /**
     * Apply translations to page elements
     */
    applyTranslations(translations) {
        // Find all elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            if (translations[key]) {
                element.textContent = translations[key];
            }
        });
        
        // Find all elements with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
            const key = element.getAttribute('data-i18n-placeholder');
            if (translations[key]) {
                element.placeholder = translations[key];
            }
        });
    }

    /**
     * Setup event listeners for system theme changes
     */
    setupEventListeners() {
        // Listen for system theme change
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem(this.storagePrefix + 'theme')) {
                    this.applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    /**
     * Get current theme
     */
    getTheme() {
        return this.currentTheme;
    }

    /**
     * Get current language
     */
    getLanguage() {
        return this.currentLanguage;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.themeLanguageManager = new ThemeLanguageManager();
});
