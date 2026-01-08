<?php
/**
 * Authentication middleware - include at the top of protected files
 */

require_once __DIR__ . '/src/Auth.php';

// Initialize authentication
$auth = new Auth();

// Check if any users exist
if (!$auth->hasUsers()) {
    // No users exist, redirect to setup
    header('Location: setup.php');
    exit;
}

// Check authentication status
if ($auth->isAuthenticated()) {
    $authenticated = true;
    $currentUser = $auth->getCurrentUser();
} else {
    $authenticated = false;
    $currentUser = null;
}

// If not authenticated, redirect to login
if (!$authenticated) {
    header('Location: login.php');
    exit;
}
?>

