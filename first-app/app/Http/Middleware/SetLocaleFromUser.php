<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->language, ['en', 'bn'], true)) {
            app()->setLocale($user->language);
        } else {
            app()->setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}
