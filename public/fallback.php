<?php
// Fallback page in case Laravel fails to boot
header('Content-Type: application/json');

$diagnostics = [
    'status' => 'emergency_fallback',
    'message' => 'The Laravel application failed to start, this is a fallback page',
    'time' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'environment' => [
        'laravel_env' => getenv('APP_ENV'),
        'debug' => getenv('APP_DEBUG'),
        'app_key_set' => !empty(getenv('APP_KEY')),
    ],
    'database' => [
        'driver' => getenv('DB_CONNECTION') ?: getenv('DB_DRIVER'),
        'host_defined' => !empty(getenv('DB_HOST')) || !empty(getenv('MYSQLHOST')) || !empty(getenv('DATABASE_URL')),
        'database_defined' => !empty(getenv('DB_DATABASE')) || !empty(getenv('MYSQLDATABASE')),
        'available_env_vars' => array_filter($_ENV, function($key) {
            return strpos($key, 'DB_') === 0 || 
                  strpos($key, 'MYSQL') === 0 || 
                  $key === 'DATABASE_URL';
        }, ARRAY_FILTER_USE_KEY),
    ],
    'server' => $_SERVER,
];

try {
    // Try to connect to the database directly as a final test
    if (extension_loaded('pdo_mysql')) {
        $dbHost = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
        $dbPort = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
        $dbName = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'forge';
        $dbUser = getenv('DB_USERNAME') ?: getenv('MYSQLUSER') ?: getenv('MYSQLUSERNAME') ?: 'forge';
        $dbPass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
        
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
        $diagnostics['database']['connection_attempt'] = [
            'dsn' => $dsn,
            'username' => $dbUser,
        ];
        
        $dbh = new PDO($dsn, $dbUser, $dbPass);
        $diagnostics['database']['connection_success'] = true;
        $diagnostics['database']['tables'] = [];
        
        $result = $dbh->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $diagnostics['database']['tables'][] = $row[0];
            }
        }
    } else {
        $diagnostics['database']['pdo_mysql_missing'] = true;
    }
} catch (Exception $e) {
    $diagnostics['database']['connection_error'] = $e->getMessage();
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT); 