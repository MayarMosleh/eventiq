<?php

use App\Http\Controllers\profileController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
route::post('register',[UserController::class,'register']);
route::post('login',[UserController::class,'login']);

Route::middleware('auth:sanctum')->group(function()
{
route::post('logout',[UserController::class,'logout']);
route::get('getAllUsers',[UserController::class,'index'])->middleware('CheckUser');
route::get('getUser/{id}',[UserController::class,'show'])->middleware('CheckUser');


route::apiResource('profiles',profileController::class);
});


