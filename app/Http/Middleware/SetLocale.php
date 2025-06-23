<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->header('Accept-Language', $request->query('lang', 'en'));

        $availableLanguages = ['en', 'ar'];

        if (!in_array($lang, $availableLanguages)) {
            $lang = 'en'; 
        }

        App::setLocale($lang);

        return $next($request);
    }
}
