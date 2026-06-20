<?php

namespace JeffersonGoncalves\LocaleCookie\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = config('locale-cookie.cookie', 'locale');
        $supported = config('locale-cookie.supported', ['en']);
        $fallback = config('locale-cookie.fallback') ?? config('app.fallback_locale');

        $locale = $request->cookie($cookieName);

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $fallback;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
