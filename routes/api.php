<?php

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

route::post('register',[UserController::class,'register']);
route::post('login',[UserController::class,'login']);


Route::middleware('auth:sanctum',EnsureEmailIsVerified::class)->group(function()
{

route::post('logout',[UserController::class,'logout']);
route::get('getAllUsers',[UserController::class,'index'])->middleware('CheckUser');
route::get('getUser/{id}',[UserController::class,'show'])->middleware('CheckUser');

route::apiResource('profiles',profileController::class);
});

Route::middleware('auth:sanctum')->group(function(){
    route::post('send-verification-code',[VerificationController::class,'send'])->middleware(VerifyCodeRateLimit::class);
    route::post('verify-verification-code',[VerificationController::class,'verify']);
});

