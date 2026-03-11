<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    // Auth routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public OTP verification (email + otp in body, no token)
    Route::post('/email/verify', [AuthController::class, 'verifyEmail'])
        ->middleware('throttle:5,1');
    Route::post('/email/resend', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:3,1');

    // Public password reset (no auth required)
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:3,1');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);

        // Logout routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});