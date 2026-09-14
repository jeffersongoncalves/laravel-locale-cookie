<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cookie Name
    |--------------------------------------------------------------------------
    |
    | The name of the cookie the middleware reads the visitor's locale from.
    |
    */

    'cookie' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The whitelist of locales the application accepts. The cookie value is
    | validated against this list; any value that is not present here is
    | ignored and the fallback locale is used instead.
    |
    */

    'supported' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The locale applied when the cookie is missing or holds an unsupported
    | value. When left as null, the middleware falls back to the framework's
    | own config('app.fallback_locale').
    |
    */

    'fallback' => null,

    /*
    |--------------------------------------------------------------------------
    | Locale Switch Route
    |--------------------------------------------------------------------------
    |
    | A ready-made route that persists the chosen locale in the cookie and
    | redirects back to a same-host Referer (open-redirect safe). Point your
    | language switcher at route(name, ['locale' => $code]). The `{locale}`
    | param is constrained to the `supported` list above.
    |
    */

    'switch' => [
        'enabled' => true,
        'path' => 'locale/{locale}',
        'name' => 'locale.switch',
        // Cookie lifetime in minutes (default: 1 year).
        'lifetime' => 60 * 24 * 365,
        // Middleware applied to the switch route. `web` is included so the
        // queued cookie is attached to the response (and the session/CSRF
        // cookies behave normally). The locale cookie itself is excluded from
        // EncryptCookies by the package, so it is written raw and read raw by
        // the SetLocale middleware. Add your own here (e.g. security headers).
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | URL-Prefix Locale Mode
    |--------------------------------------------------------------------------
    |
    | Opt-in, backward compatible. When enabled, routes registered through
    | `LocaleCookie::routes()` are registered twice: once unprefixed for the
    | default locale (preserving existing indexed URLs), and once per locale
    | listed in `segments` under a `/{segment}` prefix (e.g. `/fr/projects`).
    | This makes every non-default locale crawlable at its own URL, which the
    | cookie-only approach cannot do (bots never send a locale cookie).
    |
    */

    'url_prefix' => [
        'enabled' => env('LOCALE_COOKIE_URL_PREFIX', false),

        // Falls back to `fallback` above when null.
        'default_locale' => null,

        // Only non-default locales need an entry — the default locale is
        // never prefixed. Lets a locale code differ from its URL segment
        // (e.g. `pt_BR` => `pt-br`).
        'segments' => [
            // 'en' => 'en',
            // 'fr' => 'fr',
        ],
    ],

];
