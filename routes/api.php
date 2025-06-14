<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\VerifyCodeRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



route::post('register', [UserController::class, 'register']);
route::post('login', [UserController::class, 'login']);
route::post('requestPasswordReset', [UserController::class, 'requestPasswordReset']);
route::post('resetPassword', [UserController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {

    route::post('logout', [UserController::class, 'logout']);

    Route::middleware('verified')->group(function () {

        route::apiResource('profiles', profileController::class);

        Route::middleware('CheckProvider')->group(function () {
            route::get('getAllUsers', [UserController::class, 'index']);
            route::get('getUser/{id}', [UserController::class, 'show']);
            route::apiResource('company', CompanyController::class);
            Route::post('/event-requests', [EventRequestController::class, 'store']);

            Route::apiResource('venues', VenueController::class);
        });

        route::get('showEvents', [EventController::class, 'showEvents']);
        route::get('showProviders', [CompanyController::class, 'showProviders']);
        route::get('showServices', [ServiceController::class, 'ShowServices']);
        route::get('showVenue', [VenueController::class, 'showVenue']);
        route::post('createBooking', [BookingController::class, 'createBooking']);
        route::post('selectEvent', [BookingController::class, 'selectEvent']);
        route::post('selectProvider', [BookingController::class, 'selectProvider']);
        route::post('selectVenue', [BookingController::class, 'selectVenue']);
        route::post('selectService', [BookingController::class, 'selectService']);
        route::post('confirmBooking', [BookingController::class, 'confirmBooking']);
        route::delete('deleteServiceBooking', [BookingController::class, 'deleteServiceBooking']);
        route::patch('updateQuantityService', [BookingController::class, 'updateQuantityService']);
        route::delete('deleteVenue', [BookingController::class, 'deleteVenue']);

        route::apiResource('company', CompanyController::class);
        route::post('logout', [UserController::class, 'logout']);
    });

    Route::post('/company/search', [CompanyController::class, 'search']);

    Route::middleware('CheckAdmin')->group(function () {

        Route::get('/event-requests', [EventRequestController::class, 'index']);
        Route::post('/event-requests/{id}', [EventRequestController::class, 'adminResponse']);
    });

    Route::post('send-verification-code', [VerificationController::class, 'send'])->middleware(VerifyCodeRateLimit::class);
    Route::post('verify-verification-code', [VerificationController::class, 'verify']);

    Route::get('companies', [CompanyController::class, 'index'])->middleware('auth:sanctum');

    Route::get('venues/{venue}', [VenueController::class, 'show'])->middleware('auth:sanctum');

});
