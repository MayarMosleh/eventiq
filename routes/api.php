<?php

use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\VerifyCodeRateLimit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum', EnsureEmailIsVerified::class])->group(function () {

    Route::post('logout', [UserController::class, 'logout']);

    Route::get('getAllUsers', [UserController::class, 'index'])->middleware('CheckUser');
    Route::get('getUser/{id}', [UserController::class, 'show'])->middleware('CheckUser');

    Route::apiResource('profiles', profileController::class);

    Route::middleware('CheckProvider')->group(function () {
        Route::get('getAllUsers', [UserController::class, 'index']);
        Route::get('getUser/{id}', [UserController::class, 'show']);

        Route::post('/event-requests', [EventRequestController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('send-verification-code', [VerificationController::class, 'send'])->middleware(VerifyCodeRateLimit::class);
        Route::post('verify-verification-code', [VerificationController::class, 'verify']);
    });

    Route::middleware('CheckAdmin')->group(function () {
        Route::get('/event-requests', [EventRequestController::class, 'index']);
        Route::put('/event-requests/{id}', [EventRequestController::class, 'update']);
    });
});
