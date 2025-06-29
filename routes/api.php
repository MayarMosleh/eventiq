<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\NotifyController;
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
    // register functionality
    route::post('register', [UserController::class, 'register']);
    route::post('login', [UserController::class, 'login']);
    route::post('requestPasswordReset', [UserController::class, 'requestPasswordReset']);
    route::post('resetPassword', [UserController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('send-verification-code', [VerificationController::class, 'send'])->middleware(VerifyCodeRateLimit::class);
        Route::post('verify-verification-code', [VerificationController::class, 'verify']);

        Route::get('/companies/{company}/events', [CompanyController::class, 'indexCompanyEvents']);




        Route::middleware('verified')->group(function () {

            // profile routes
            route::apiResource('profiles', profileController::class);
            route::post('logout', [UserController::class, 'logout']);
            route::delete('deleteAccount', [UserController::class, 'deleteAccount']);


            // Profile Update
            Route::patch('/profile/{id}', [ProfileController::class, 'updateInfo']);
            Route::post('/profile/{id}', [profileController::class, 'updateImage']);


            // create booking routes
            route::post('createBooking', [BookingController::class, 'createBooking']);
            route::post('selectEvent', [BookingController::class, 'selectEvent']);
            route::post('selectProvider', [BookingController::class, 'selectProvider']);
            route::post('selectVenue', [BookingController::class, 'selectVenue']);
            route::post('selectService', [BookingController::class, 'selectService']);
            route::post('confirmBooking', [BookingController::class, 'confirmBooking']);


            // update booking
            route::delete('deleteServiceBooking', [BookingController::class, 'deleteServiceBooking']);
            route::patch('updateQuantityService', [BookingController::class, 'updateQuantityService']);
            route::delete('cancelBooking', [BookingController::class, 'cancelBooking']);


            // Venue Routes
            route::delete('deleteVenue', [BookingController::class, 'deleteVenue']);
            Route::get('/venues/{venue}', [VenueController::class, 'show']);
            Route::get('/venues/{company}', [VenueController::class, 'getCompanyVenues']);


            // Rating Routes
            Route::get('/companies/{company}/rating', [CompanyController::class, 'getAverageRating']);
            Route::post('/ratings', [RatingController::class, 'store']);


            // company routes
            Route::get('/companies', [CompanyController::class, 'index']);
            route::get('showProviders', [CompanyController::class, 'showProviders']);
            Route::post('/company/search', [CompanyController::class, 'search']);
            route::get('showServices', [ServiceController::class, 'ShowServices']);
            route::get('showVenue', [VenueController::class, 'showVenue']);


            // events Routes
            route::get('showEvents', [EventController::class, 'showEvents']);


            route::post('createAccountStripe', [StripeConnectController::class, 'connect']);
            route::post('payment', [StripeConnectController::class, 'pay'])->middleware('CheckStripeAccount');
            route::get('getAccountStatus', [StripeConnectController::class, 'getAccountStatus']);
            route::get('getStripeAccountId', [StripeConnectController::class, 'getStripeAccountId']);


            // notifications
            Route::get('/notifications', [NotifyController::class, 'index']);
            Route::delete('/notifications/{id}', [NotifyController::class, 'destroy']);
        });

        Route::middleware('CheckProvider')->group(function () {
            Route::post('/event-requests', [EventRequestController::class, 'store']);
            Route::post('/device-token', [DeviceTokenController::class, 'store']);
            route::apiResource('company', CompanyController::class);
            Route::post('/company/add-events', [CompanyController::class, 'addEventToCompany']);

            // Service Routes
            Route::post('/services', [ServiceController::class, 'store']);
            Route::post('servicesAddImage', [ServiceController::class, 'addImage']);


            // Venue Routes
            Route::apiResource('venues', VenueController::class);
            Route::post('venuesAddImage', [VenueController::class, 'addImage']);
            Route::get('venueGetImages',[VenueController::class, 'getImages']);

            Route::apiResource('services', ServiceController::class);

            // Update Company Routes
            Route::patch('/company/{id}/info', [CompanyController::class, 'updateInfo']);
            Route::post('/company/{id}/image', [CompanyController::class, 'updateImage']);
        });
        Route::get('servicesGetImage',[ServiceController::class, 'getImages']);
        Route::middleware('CheckAdmin')->group(function () {
            // Event Request Routes
            Route::get('/event-requests', [EventRequestController::class, 'index']);
            Route::post('/event-requests/{id}', [EventRequestController::class, 'adminResponse']);
            Route::delete('/event-requests/{id}', [EventRequestController::class, 'destroyAnsweredRequest']);

            // User Routes
            route::get('getAllUsers', [UserController::class, 'index']);
            route::get('getUser/{id}', [UserController::class, 'show']);

            // Event Routes
            route::post('addEventAdmin',[EventController::class, 'addEventAdmin']);
            route::delete('deleteEventAdmin',[EventController::class, 'deleteEventAdmin']);
            route::post('addImageEvent',[EventController::class, 'addImageEvent']);
        });
    });
});
