<?php
/**
 * Railway Environment Variable Setup
 * 
 * This script automatically sets required environment variables for Railway deployment.
 * It checks current environment variables and only adds ones that are missing.
 */

// Only run in the Railway environment
if (!getenv('RAILWAY_ENVIRONMENT')) {
    echo "This script is intended to run only in the Railway environment\n";
    exit(1);
}

// Required variables that must be set
$requiredVariables = [
    'APP_KEY' => null, // Must be set manually
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'cookie',
    'SESSION_SECURE_COOKIE' => 'true',
    'BROADCAST_DRIVER' => 'log',
    'CACHE_DRIVER' => 'file',
    'FILESYSTEM_DISK' => 'local',
    'QUEUE_CONNECTION' => 'sync',
    'DB_CONNECTION' => 'mysql',
];

// Map Railway database variables to Laravel format
if (getenv('MYSQLHOST') && !getenv('DB_HOST')) {
    echo "Setting DB_HOST from MYSQLHOST\n";
    putenv("DB_HOST=" . getenv('MYSQLHOST'));
}

if (getenv('MYSQLPORT') && !getenv('DB_PORT')) {
    echo "Setting DB_PORT from MYSQLPORT\n";
    putenv("DB_PORT=" . getenv('MYSQLPORT'));
}

if (getenv('MYSQLDATABASE') && !getenv('DB_DATABASE')) {
    echo "Setting DB_DATABASE from MYSQLDATABASE\n";
    putenv("DB_DATABASE=" . getenv('MYSQLDATABASE'));
}

if (getenv('MYSQLUSER') && !getenv('DB_USERNAME')) {
    echo "Setting DB_USERNAME from MYSQLUSER\n";
    putenv("DB_USERNAME=" . getenv('MYSQLUSER'));
}

if (getenv('MYSQLUSERNAME') && !getenv('DB_USERNAME') && !getenv('MYSQLUSER')) {
    echo "Setting DB_USERNAME from MYSQLUSERNAME\n";
    putenv("DB_USERNAME=" . getenv('MYSQLUSERNAME'));
}

if (getenv('MYSQLPASSWORD') && !getenv('DB_PASSWORD')) {
    echo "Setting DB_PASSWORD from MYSQLPASSWORD\n";
    putenv("DB_PASSWORD=" . getenv('MYSQLPASSWORD'));
}

// Set required variables if missing
foreach ($requiredVariables as $key => $defaultValue) {
    if (!getenv($key) && $defaultValue !== null) {
        echo "Setting $key to default: $defaultValue\n";
        putenv("$key=$defaultValue");
    } elseif (!getenv($key)) {
        echo "WARNING: $key is not set and has no default\n";
    }
}

echo "Environment setup complete\n"; 