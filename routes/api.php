<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\VerifyCodeRateLimit;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

route::post('register', [UserController::class, 'register']);
route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    route::post('logout', [UserController::class, 'logout']);

    Route::middleware('verified')->group(function () {


        route::get('showEvents', [EventController::class, 'index']);
        route::get('showProviders', [CompanyController::class, 'providers']);
        route::get('showServices', [ServiceController::class, 'ShowServices']);


        route::apiResource('profiles', profileController::class);
        Route::middleware('CheckProvider')->group(function () {
            route::get('getAllUsers', [UserController::class, 'index']);
            route::get('getUser/{id}', [UserController::class, 'show']);
            Route::post('/event-requests', [EventRequestController::class, 'store']);
        });

    });


    Route::middleware('CheckAdmin')->group(function () {
        Route::get('/event-requests', [EventRequestController::class, 'index']);
        Route::put('/event-requests/{id}', [EventRequestController::class, 'update']);
    });

    Route::post('send-verification-code', [VerificationController::class, 'send'])->middleware(VerifyCodeRateLimit::class);
    Route::post('verify-verification-code', [VerificationController::class, 'verify']);


});
