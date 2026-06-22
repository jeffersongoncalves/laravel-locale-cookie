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
        // queued cookie is attached + encrypted the same way the SetLocale
        // middleware reads it; add your own (e.g. a security-headers one).
        'middleware' => ['web'],
    ],

];
