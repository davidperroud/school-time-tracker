<?php
require_once __DIR__ . '/../src/Auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to home page
header('Location: index.php');
exit;
