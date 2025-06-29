<?php

use App\Http\Middleware\CheckIfAdmin;
use App\Http\Middleware\CheckStripeAccount;
use App\Http\Middleware\checkUserRole;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyCodeRateLimit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'CheckProvider'=> CheckUserRole::class,
            'CheckUser'=>checkUserRole::class,
            'verified'     => EnsureEmailIsVerified::class,
            'limit'        => VerifyCodeRateLimit::class,
            'CheckAdmin'   => CheckIfAdmin::class,
            'lang'         =>SetLocale::class,
            'CheckStripeAccount' =>CheckStripeAccount::class]);
        })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
