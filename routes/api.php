<?php

use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

// Apply ForceJsonResponse middleware to all API routes
Route::middleware([ForceJsonResponse::class])->group(function () {

// Authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    
    // Protected QR code generation route (GET only)
    Route::get('generate-qr', [QrCodeController::class, 'generateQr'])->name('generate.qr');
});

Route::options('generate-qr', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

}); // End of ForceJsonResponse middleware group