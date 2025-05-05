<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        
        // Log database connection info for debugging
        try {
            if (env('APP_ENV') === 'production') {
                Log::info('Database config: ' . json_encode([
                    'connection' => env('DB_CONNECTION'),
                    'host' => env('DB_HOST', 'not set'),
                    'database' => env('DB_DATABASE', 'not set'),
                    'username' => env('DB_USERNAME', 'not set'),
                    'port' => env('DB_PORT', 'not set'),
                ]));
            }
        } catch (Exception $e) {
            Log::error('Failed to log DB config: ' . $e->getMessage());
        }
    }
}
