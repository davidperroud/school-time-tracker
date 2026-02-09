<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUser($username, $password, $languagePreference = 'fr', $isAdmin = false) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $result = $this->db->execute(
            "INSERT INTO users (username, password_hash, language_preference, is_admin) VALUES (?, ?, ?, ?)",
            [$username, $passwordHash, $languagePreference, $isAdmin ? 1 : 0]
        );

        if ($result) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Authentifier un utilisateur
     */
    public function authenticate($username, $password) {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );

        if ($user && password_verify($password, $user['password_hash'])) {
            // Mettre à jour la dernière connexion
            $this->db->execute(
                "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
                [$user['id']]
            );

            return $user;
        }

        return false;
    }

    /**
     * Récupérer un utilisateur par ID
     */
    public function getUserById($id) {
        return $this->db->fetchOne(
            "SELECT id, username, language_preference, is_admin, created_at, last_login FROM users WHERE id = ?",
            [$id]
        );
    }

    /**
     * Récupérer un utilisateur par nom d'utilisateur
     */
    public function getUserByUsername($username) {
        return $this->db->fetchOne(
            "SELECT id, username, language_preference, is_admin, created_at, last_login FROM users WHERE username = ?",
            [$username]
        );
    }

    /**
     * Mettre à jour la préférence de langue d'un utilisateur
     */
    public function updateLanguagePreference($userId, $language) {
        $availableLanguages = ['fr', 'en', 'de', 'it'];

        if (!in_array($language, $availableLanguages)) {
            return false;
        }

        return $this->db->execute(
            "UPDATE users SET language_preference = ? WHERE id = ?",
            [$language, $userId]
        );
    }

    /**
     * Mettre à jour le mot de passe d'un utilisateur
     */
    public function updatePassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        return $this->db->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $userId]
        );
    }

    /**
     * Récupérer tous les utilisateurs (pour l'administration)
     */
    public function getAllUsers() {
        return $this->db->fetchAll(
            "SELECT id, username, language_preference, is_admin, created_at, last_login FROM users ORDER BY username"
        );
    }

    /**
     * Mettre à jour les détails d'un utilisateur
     */
    public function updateUser($userId, $username, $languagePreference, $isAdmin = null) {
        $availableLanguages = ['fr', 'en', 'de', 'it'];

        if (!in_array($languagePreference, $availableLanguages)) {
            return false;
        }

        // Vérifier si le nom d'utilisateur existe déjà pour un autre utilisateur
        $existingUser = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = ? AND id != ?",
            [$username, $userId]
        );

        if ($existingUser) {
            return false;
        }

        if ($isAdmin !== null) {
            return $this->db->execute(
                "UPDATE users SET username = ?, language_preference = ?, is_admin = ? WHERE id = ?",
                [$username, $languagePreference, $isAdmin ? 1 : 0, $userId]
            );
        } else {
            return $this->db->execute(
                "UPDATE users SET username = ?, language_preference = ? WHERE id = ?",
                [$username, $languagePreference, $userId]
            );
        }
    }

    /**
     * Vérifier si un utilisateur est administrateur
     */
    public function isAdmin($userId) {
        $user = $this->db->fetchOne(
            "SELECT is_admin FROM users WHERE id = ?",
            [$userId]
        );

        return $user && $user['is_admin'] == 1;
    }

    /**
     * Vérifier s'il y a des utilisateurs dans la base de données
     */
    public function hasUsers() {
        $count = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users"
        );

        return $count['count'] > 0;
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($userId) {
        return $this->db->execute(
            "DELETE FROM users WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Vérifier si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username) {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = ?",
            [$username]
        );

        return $user !== null;
    }

    /**
     * Créer un jeton de réinitialisation de mot de passe
     * Génère un token unique et un code PIN, stocke en base
     */
    public function createPasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $pin = sprintf('%06d', random_int(0, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $result = $this->db->execute(
            "INSERT INTO password_resets (user_id, token, pin, expires_at) VALUES (?, ?, ?, ?)",
            [$userId, $token, $pin, $expiresAt]
        );

        if ($result) {
            return [
                'token' => $token,
                'pin' => $pin,
                'expires_at' => $expiresAt
            ];
        }

        return false;
    }

    /**
     * Valider un jeton de réinitialisation
     * Vérifie que le token existe, n'est pas expiré et n'est pas encore utilisé
     */
    public function validateResetToken($token) {
        $record = $this->db->fetchOne(
            "SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > datetime('now')",
            [$token]
        );

        if ($record) {
            return [
                'user_id' => $record['user_id'],
                'pin' => $record['pin'],
                'expires_at' => $record['expires_at']
            ];
        }

        return false;
    }

    /**
     * Valider un code PIN de réinitialisation
     */
    public function validateResetPin($pin) {
        $record = $this->db->fetchOne(
            "SELECT * FROM password_resets WHERE pin = ? AND used_at IS NULL AND expires_at > datetime('now')",
            [$pin]
        );

        if ($record) {
            return [
                'user_id' => $record['user_id'],
                'token' => $record['token'],
                'expires_at' => $record['expires_at']
            ];
        }

        return false;
    }

    /**
     * Marquer un jeton de réinitialisation comme utilisé
     */
    public function markTokenAsUsed($token) {
        return $this->db->execute(
            "UPDATE password_resets SET used_at = datetime('now') WHERE token = ?",
            [$token]
        );
    }

    /**
     * Marquer un jeton comme utilisé par PIN
     */
    public function markPinAsUsed($pin) {
        return $this->db->execute(
            "UPDATE password_resets SET used_at = datetime('now') WHERE pin = ?",
            [$pin]
        );
    }

    /**
     * Nettoyer les jetons de réinitialisation expirés
     */
    public function cleanupExpiredTokens() {
        return $this->db->execute(
            "DELETE FROM password_resets WHERE expires_at <= datetime('now') OR used_at IS NOT NULL"
        );
    }

    /**
     * Récupérer le user ID par token de réinitialisation
     */
    public function getUserIdByResetToken($token) {
        $record = $this->db->fetchOne(
            "SELECT user_id FROM password_resets WHERE token = ?",
            [$token]
        );

        return $record ? $record['user_id'] : null;
    }
}