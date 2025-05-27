<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class VerifyCodeRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = auth()->user()->email;
        $key = 'send-code:' . $email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'message' => 'Too many requests. Please try again after 5 minutes.'
            ], 429);
        }

        RateLimiter::hit($key, 300);

        return $next($request);
    }
}
