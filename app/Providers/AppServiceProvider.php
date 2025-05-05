<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Set default MySQL host if not provided
        if (env('DB_CONNECTION') === 'mysql' && empty(env('DB_HOST'))) {
            config(['database.connections.mysql.host' => '127.0.0.1']);
        }
        
        // Check for Railway-specific environment variables and map them
        if (empty(env('DB_HOST')) && !empty(env('MYSQLHOST'))) {
            config(['database.connections.mysql.host' => env('MYSQLHOST')]);
        }
        
        if (empty(env('DB_PORT')) && !empty(env('MYSQLPORT'))) {
            config(['database.connections.mysql.port' => env('MYSQLPORT')]);
        }
        
        if (empty(env('DB_DATABASE')) && !empty(env('MYSQLDATABASE'))) {
            config(['database.connections.mysql.database' => env('MYSQLDATABASE')]);
        }
        
        if (empty(env('DB_USERNAME')) && !empty(env('MYSQLUSER'))) {
            config(['database.connections.mysql.username' => env('MYSQLUSER')]);
        } 
        
        if (empty(env('DB_USERNAME')) && !empty(env('MYSQLUSERNAME'))) {
            config(['database.connections.mysql.username' => env('MYSQLUSERNAME')]);
        }
        
        if (empty(env('DB_PASSWORD')) && !empty(env('MYSQLPASSWORD'))) {
            config(['database.connections.mysql.password' => env('MYSQLPASSWORD')]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        
        // Set a reasonable default string length for MySQL
        Schema::defaultStringLength(191);
        
        // Register an error handler for database connectivity issues
        $this->handleDatabaseErrors();
        
        // Log configuration information for debugging
        $this->logConfigurationInfo();
    }
    
    /**
     * Handle database connection errors gracefully
     */
    protected function handleDatabaseErrors(): void
    {
        DB::connection()->setReconnector(function ($connection) {
            Log::warning('Database connection lost. Attempting to reconnect...');
            $connection->reconnect();
        });
    }
    
    /**
     * Log important configuration information for debugging
     */
    protected function logConfigurationInfo(): void
    {
        try {
            if (env('APP_ENV') === 'production') {
                Log::info('Environment configuration:', [
                    'app_env' => env('APP_ENV'),
                    'app_url' => env('APP_URL'),
                    'app_debug' => env('APP_DEBUG'),
                    'app_key_set' => !empty(env('APP_KEY')),
                ]);
                
                Log::info('Database configuration:', [
                    'connection' => config('database.default'),
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database' => config('database.connections.mysql.database'),
                    'username' => config('database.connections.mysql.username'),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to log configuration info: ' . $e->getMessage());
        }
    }
}
