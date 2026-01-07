<?php
// Fichier d'authentification - à mettre en haut de chaque fichier protégé

$valid_username = 'username';
$valid_password = 'password';

// Tentative d'authentification
$authenticated = false;

// Vérifier les différentes sources possibles d'authentification
if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    // Méthode 1: Variables PHP_AUTH
    if ($_SERVER['PHP_AUTH_USER'] === $valid_username && 
        $_SERVER['PHP_AUTH_PW'] === $valid_password) {
        $authenticated = true;
    }
} else {
    // Méthode 2: Header Authorization
    $auth_header = '';
    
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    
    if (!empty($auth_header) && strpos($auth_header, 'Basic ') === 0) {
        $credentials = base64_decode(substr($auth_header, 6));
        if (strpos($credentials, ':') !== false) {
            list($user, $pass) = explode(':', $credentials, 2);
            if ($user === $valid_username && $pass === $valid_password) {
                $authenticated = true;
            }
        }
    }
}

// Si pas authentifié, demander l'authentification
if (!$authenticated) {
    header('WWW-Authenticate: Basic realm="Study Tracker - Acces protege"');
    header('HTTP/1.0 401 Unauthorized');
    header('Content-Type: text/plain');
    die('401 Unauthorized - Invalid credentials or authentication required');
}
?>
