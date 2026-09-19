<?php

declare(strict_types=1);

namespace JeffersonGoncalves\LocaleCookie\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = config('locale-cookie.cookie', 'locale');
        $supported = config('locale-cookie.supported', ['en']);
        $fallback = config('locale-cookie.fallback') ?? config('app.fallback_locale') ?? 'en';

        // In URL-prefix mode, the matched route's `locale` action (set by
        // `LocaleCookie::routes()`'s groups) is authoritative: the same URL
        // must always render the same locale for anyone, bots included, so it
        // takes priority over the cookie rather than the other way round.
        // Falls back to a `{locale}` route parameter for apps that register
        // every locale under a single dynamic prefix group instead.
        $urlPrefixEnabled = (bool) config('locale-cookie.url_prefix.enabled', false);

        $routeLocale = $urlPrefixEnabled ? $request->route()?->getAction('locale') : null;

        if ($urlPrefixEnabled && (! is_string($routeLocale) || ! in_array($routeLocale, $supported, true))) {
            $routeLocale = $request->route('locale');
        }

        if (is_string($routeLocale) && in_array($routeLocale, $supported, true)) {
            $locale = $routeLocale;

            // So `route()`/link generation picks up the URL-derived locale
            // without it having to be passed explicitly at every call site.
            URL::defaults(['locale' => $locale]);
        } else {
            // Read the raw cookie value via the Symfony bag so it bypasses Laravel's
            // EncryptCookies middleware. This keeps client-set (e.g. JavaScript) cookies
            // working, which would otherwise fail to decrypt and silently resolve to null.
            $locale = $request->cookies->get($cookieName);

            if (! is_string($locale) || ! in_array($locale, $supported, true)) {
                $locale = $fallback;
            }
        }

        App::setLocale($locale);

        // Keep the cookie in sync with the URL-derived locale, so any code
        // that reads it directly (e.g. client-side JS) sees the same value.
        if (is_string($routeLocale) && $routeLocale === $locale && $request->cookies->get($cookieName) !== $locale) {
            Cookie::queue($cookieName, $locale, (int) config('locale-cookie.switch.lifetime', 60 * 24 * 365), '/', null, (bool) config('session.secure', false), false, false, 'lax');
        }

        return $next($request);
    }
}
