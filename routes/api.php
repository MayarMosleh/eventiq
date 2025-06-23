<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StripeConnectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\VerifyCodeRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('lang')->group(function () {
        route::post('register', [UserController::class, 'register']);
        route::post('login', [UserController::class, 'login']);
        route::post('requestPasswordReset', [UserController::class, 'requestPasswordReset']);
        route::post('resetPassword', [UserController::class, 'resetPassword']);
        Route::post('send-verification-code', [VerificationController::class, 'send'])->middleware(VerifyCodeRateLimit::class);
        Route::post('verify-verification-code', [VerificationController::class, 'verify']);
Route::middleware('auth:sanctum')->group(function () {
Route::middleware('verified')->group(function () {

        route::apiResource('profiles', profileController::class);
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
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::get('/venues/{venue}', [VenueController::class, 'show']);
        Route::get('/companies/{company}/rating', [RatingController::class, 'getCompanyRating']);
        Route::post('/ratings', [RatingController::class, 'store']);
        route::post('logout', [UserController::class, 'logout']);
        Route::post('/company/search', [CompanyController::class, 'search']);
        route::post('createAccountStripe', [StripeConnectController::class, 'connect']);
        route::post('payment', [StripeConnectController::class, 'pay'])->middleware('CheckStripeAccount');
        route::delete('cancelBooking', [BookingController::class, 'cancelBooking']);
    });
    Route::middleware('CheckProvider')->group(function () {
         Route::post('/event-requests', [EventRequestController::class, 'store']);
         Route::post('/device-token', [DeviceTokenController::class, 'store']);
         Route::apiResource('venues', VenueController::class);
         route::apiResource('company', CompanyController::class);
        });
    Route::middleware('CheckAdmin')->group(function () {
        Route::get('/event-requests', [EventRequestController::class, 'index']);
        Route::post('/event-requests/{id}', [EventRequestController::class, 'adminResponse']);
        route::get('getAllUsers', [UserController::class, 'index']);
        route::get('getUser/{id}', [UserController::class, 'show']);
    });
});
});