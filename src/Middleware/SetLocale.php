<?php

declare(strict_types=1);

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
        $fallback = config('locale-cookie.fallback') ?? config('app.fallback_locale') ?? 'en';

        // Read the raw cookie value via the Symfony bag so it bypasses Laravel's
        // EncryptCookies middleware. This keeps client-set (e.g. JavaScript) cookies
        // working, which would otherwise fail to decrypt and silently resolve to null.
        $locale = $request->cookies->get($cookieName);

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $fallback;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
