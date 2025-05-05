<?php
// Test file to check if PHP is executing
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'PHP is working properly',
    'environment' => $_SERVER['APP_ENV'] ?? 'not set',
    'server' => $_SERVER,
    'env' => getenv(),
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s')
]); 