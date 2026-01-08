<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/User.php';

/**
 * Authentication class to handle different authentication methods
 * Separates authentication logic from session management
 */
class Auth {
    private $session;
    private $user;

    public function __construct() {
        $this->session = new Session();
        $this->user = new User();
    }

    /**
     * Authenticate user with username and password
     */
    public function authenticate($username, $password) {
        return $this->user->authenticate($username, $password);
    }

    /**
     * Authenticate via HTTP Basic auth
     */
    public function authenticateBasic($username, $password) {
        if ($this->authenticate($username, $password)) {
            $userData = $this->user->getUserByUsername($username);
            $this->session->login($userData);
            return true;
        }
        return false;
    }

    /**
     * Login user and create session
     */
    public function login($userData) {
        $this->session->login($userData);
    }

    /**
     * Logout user and destroy session
     */
    public function logout() {
        $this->session->logout();
    }

    /**
     * Check if user is logged in and session is valid
     */
    public function isAuthenticated() {
        return $this->session->isLoggedIn() && !$this->session->isExpired();
    }

    /**
     * Get current authenticated user data
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $this->session->getCurrentUser();
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->session->getUserId();
    }

    /**
     * Get current username
     */
    public function getUsername() {
        return $this->session->getUsername();
    }

    /**
     * Get user's preferred language
     */
    public function getLanguagePreference() {
        return $this->session->getLanguagePreference();
    }

    /**
     * Update user's language preference
     */
    public function updateLanguagePreference($language) {
        if ($this->isAuthenticated()) {
            $userId = $this->getUserId();
            if ($this->user->updateLanguagePreference($userId, $language)) {
                $this->session->updateLanguagePreference($language);
                return true;
            }
        }
        return false;
    }

    /**
     * Check if session is expired
     */
    public function isSessionExpired() {
        return $this->session->isExpired();
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        $userId = $this->getUserId();
        if ($userId) {
            return $this->user->isAdmin($userId);
        }
        return false;
    }

    /**
     * Check if any users exist in the database
     */
    public function hasUsers() {
        return $this->user->hasUsers();
    }
}