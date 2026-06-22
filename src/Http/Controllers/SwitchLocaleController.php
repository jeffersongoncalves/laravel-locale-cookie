<?php

declare(strict_types=1);

namespace JeffersonGoncalves\LocaleCookie\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Persist the visitor's chosen locale in the cookie and redirect back. The
 * locale is validated against `locale-cookie.supported`; anything else falls
 * back to the configured fallback. The redirect target is the Referer, but only
 * when it points back at our own host — closing the open-redirect window.
 */
class SwitchLocaleController
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supported = (array) config('locale-cookie.supported', []);

        if (! in_array($locale, $supported, true)) {
            $locale = config('locale-cookie.fallback') ?? config('app.fallback_locale');
        }

        Cookie::queue(
            (string) config('locale-cookie.cookie', 'locale'),
            $locale,
            (int) config('locale-cookie.switch.lifetime', 60 * 24 * 365),
        );

        return redirect($this->safeReferer($request));
    }

    /**
     * The Referer is attacker-controllable, so only honour it when it points
     * back at our own host. Anything cross-origin (or unparseable) falls back
     * to the home page.
     */
    private function safeReferer(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if ($referer === null || $referer === '') {
            return '/';
        }

        $host = parse_url($referer, PHP_URL_HOST);

        return $host === $request->getHost() ? $referer : '/';
    }
}
