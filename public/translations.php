<?php
$lang = $_GET['lang'] ?? 'fr';
$langFile = __DIR__ . '/../lang/' . $lang . '.json';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (file_exists($langFile)) {
    echo file_get_contents($langFile);
} else {
    echo json_encode(['error' => 'Translation file not found']);
}
?>