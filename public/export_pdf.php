<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../src/ApiController.php';

$api = new ApiController();
$api->exportPDF();
