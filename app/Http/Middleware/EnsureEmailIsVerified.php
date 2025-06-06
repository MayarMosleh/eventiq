<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified.'
            ], 403);
        }

        return $next($request);
    }
}
