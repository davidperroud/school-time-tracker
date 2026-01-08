<?php

class Session {
    private const SESSION_LIFETIME = 3600 * 24 * 7; // 7 jours

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration de session sécurisée
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Strict');

            session_start();

            // Régénération périodique de l'ID de session pour la sécurité
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // Toutes les 5 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    /**
     * Connecter un utilisateur
     */
    public function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['language_preference'] = $user['language_preference'];
        $_SESSION['login_time'] = time();
    }

    /**
     * Déconnecter l'utilisateur
     */
    public function logout() {
        session_unset();
        session_destroy();
        session_start();
    }

    /**
     * Vérifier si un utilisateur est connecté
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
    }

    /**
     * Récupérer l'ID de l'utilisateur connecté
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Récupérer le nom d'utilisateur connecté
     */
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Récupérer la préférence de langue de l'utilisateur
     */
    public function getLanguagePreference() {
        return $_SESSION['language_preference'] ?? 'fr';
    }

    /**
     * Mettre à jour la préférence de langue en session
     */
    public function updateLanguagePreference($language) {
        $_SESSION['language_preference'] = $language;
    }

    /**
     * Vérifier si la session a expiré
     */
    public function isExpired() {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }

        return (time() - $_SESSION['login_time']) > self::SESSION_LIFETIME;
    }

    /**
     * Régénérer l'ID de session (pour la sécurité)
     */
    public function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Détruire complètement la session
     */
    public function destroy() {
        // Nettoyer tous les cookies de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_unset();
        session_destroy();
    }

    /**
     * Authentifier via HTTP Basic (méthode de secours)
     */
    public function authenticateBasic($user, $pass) {
        $userObj = new User();

        if ($userObj->authenticate($user, $pass)) {
            $userData = $userObj->getUserByUsername($user);
            $this->login($userData);
            return true;
        }

        return false;
    }

    /**
     * Récupérer les informations complètes de l'utilisateur connecté
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userObj = new User();
        return $userObj->getUserById($this->getUserId());
    }
}