<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes provide health check endpoints for monitoring and load
| balancers. They verify that critical services are operational.
|
*/

Route::get('/health', function () {
    $checks = [
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'services' => [],
    ];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $checks['services']['database'] = 'healthy';
    } catch (\Exception $e) {
        $checks['status'] = 'unhealthy';
        $checks['services']['database'] = 'unhealthy';
        $checks['errors']['database'] = $e->getMessage();
    }

    // Check Redis connection
    try {
        Redis::ping();
        $checks['services']['redis'] = 'healthy';
    } catch (\Exception $e) {
        $checks['status'] = 'degraded';
        $checks['services']['redis'] = 'unhealthy';
        $checks['errors']['redis'] = $e->getMessage();
    }

    // Check storage writability
    try {
        $testFile = storage_path('framework/cache/health-check-' . time());
        file_put_contents($testFile, 'test');
        unlink($testFile);
        $checks['services']['storage'] = 'healthy';
    } catch (\Exception $e) {
        $checks['status'] = 'unhealthy';
        $checks['services']['storage'] = 'unhealthy';
        $checks['errors']['storage'] = $e->getMessage();
    }

    $statusCode = $checks['status'] === 'healthy' ? 200 : 503;

    return response()->json($checks, $statusCode);
});

Route::get('/health/ready', function () {
    // Readiness check - is the application ready to serve traffic?
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'not ready',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }
});

Route::get('/health/live', function () {
    // Liveness check - is the application alive?
    return response()->json([
        'status' => 'alive',
        'timestamp' => now()->toIso8601String(),
    ], 200);
});
