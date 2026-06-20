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

];
