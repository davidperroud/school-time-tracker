<?php
class Translation {
    private $lang;
    private $translations;
    private $defaultLang = 'fr';
    private $langDir = __DIR__ . '/../lang/';

    public function __construct($lang = null) {
        $this->lang = $lang ?: $this->detectLanguage();
        $this->loadTranslations();
    }

    private function detectLanguage() {
        // Priority: User preference > GET param > cookie > browser > default

        // Vérifier d'abord la préférence utilisateur (si session active)
        if (isset($_SESSION['language_preference'])) {
            $userLang = $_SESSION['language_preference'];
            if (in_array($userLang, ['fr', 'en', 'de', 'it'])) {
                return $userLang;
            }
        }

        // Puis paramètre GET
        if (isset($_GET['lang'])) {
            return $_GET['lang'];
        }

        // Puis cookie
        if (isset($_COOKIE['lang'])) {
            return $_COOKIE['lang'];
        }

        // Puis langue du navigateur
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, ['fr', 'en', 'de', 'it'])) {
                return $browserLang;
            }
        }

        return $this->defaultLang;
    }

    private function loadTranslations() {
        $file = $this->langDir . $this->lang . '.json';
        if (!file_exists($file)) {
            $file = $this->langDir . $this->defaultLang . '.json';
        }
        $json = file_get_contents($file);
        $this->translations = json_decode($json, true);
    }

    public function t($key) {
        $keys = explode('.', $key);
        $value = $this->translations;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key;
            }
        }
        return $value;
    }

    public function getLang() {
        return $this->lang;
    }

    public function getAvailableLanguages() {
        return ['fr' => 'Français', 'en' => 'English', 'de' => 'Deutsch', 'it' => 'Italiano'];
    }
}
