<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthCheckController extends Controller
{
    public function check()
    {
        $status = [
            'environment' => app()->environment(),
            'database' => 'Not connected',
            'app_key' => env('APP_KEY') ? 'Set' : 'Not set',
            'debug' => env('APP_DEBUG') ? 'On' : 'Off',
            'url' => env('APP_URL', 'Not set'),
            'php_version' => phpversion(),
        ];
        
        try {
            DB::connection()->getPdo();
            $status['database'] = 'Connected to ' . DB::connection()->getDatabaseName();
        } catch (Exception $e) {
            $status['database_error'] = $e->getMessage();
            Log::error('Database connection error: ' . $e->getMessage());
        }
        
        return response()->json($status);
    }
} 