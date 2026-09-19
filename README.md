<div class="filament-hidden">

![Laravel Locale Cookie](https://raw.githubusercontent.com/jeffersongoncalves/laravel-locale-cookie/master/art/jeffersongoncalves-laravel-locale-cookie.png)

</div>

# Laravel Locale Cookie

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-locale-cookie.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-locale-cookie)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-locale-cookie/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-locale-cookie/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-locale-cookie/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-locale-cookie/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-locale-cookie.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-locale-cookie)

A Laravel middleware that resolves the application locale from a cookie. The requested value is validated against a configurable list of supported locales and ignored when it is missing or unknown, falling back to a sensible default. Cookie name, supported locales, and fallback locale are all driven by config.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-locale-cookie
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="locale-cookie-config"
```

This is the contents of the published config file:

```php
return [
    'cookie' => 'locale',
    'supported' => ['en'],
    'fallback' => null,
];
```

- `cookie` — the name of the cookie the middleware reads the visitor's locale from (default `locale`).
- `supported` — the whitelist of accepted locales. A cookie value that is not in this list is ignored.
- `fallback` — the locale applied when the cookie is missing or unsupported. When `null`, the middleware falls back to the framework's `config('app.fallback_locale')`.

## Usage

Register the middleware on the route group that should be locale-aware. The middleware reads the cookie, validates it against your `supported` list, and applies the matching locale (or the fallback) for the rest of the request:

```php
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

Route::middleware(SetLocale::class)->group(function () {
    // ...locale-aware routes
});
```

For convenience, the package also registers a `locale` middleware alias, so you can reference it by name:

```php
Route::middleware('locale')->group(function () {
    // ...locale-aware routes
});
```

You can also alias it in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \JeffersonGoncalves\LocaleCookie\Middleware\SetLocale::class,
    ]);
})
```

### Setting the cookie

This package only **reads** the locale cookie — it never writes it. Your application is responsible for setting the cookie when the visitor chooses a locale. You can do that from PHP:

```php
return redirect()->back()->withCookie(cookie()->forever('locale', 'pt_BR'));
```

…or from the client with JavaScript:

```js
document.cookie = 'locale=pt_BR; path=/; max-age=31536000';
```

### Encrypted cookies

By default Laravel's `EncryptCookies` middleware encrypts every cookie, and it runs **before** this middleware. To keep the read side (the `SetLocale` middleware, which reads the raw value via `$request->cookies->get()`) and the write side (the bundled switch route) consistent — and so that **client-set** cookies (e.g. the JavaScript example above, which are never encrypted) keep working — **the package automatically registers the configured cookie name in `EncryptCookies`' exception list** (via `EncryptCookies::except()`). The cookie is therefore always written and read as a plaintext value; you do not need to add it to `$except` yourself.

If you change `locale-cookie.cookie`, the exclusion follows the new name automatically.

### Using with Livewire

If your locale-aware pages use Livewire, register the middleware as a Livewire persistent middleware as well, so subsequent `/livewire/update` requests keep the visitor's locale instead of falling back to the default. Register both the route-group middleware **and** the persistent middleware (pattern taken from the source project this package was extracted from):

```php
use Livewire\Livewire;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

// In a service provider's boot() method
Livewire::addPersistentMiddleware([
    SetLocale::class,
]);
```

### Locale switch route

The package registers a ready-made route that persists the chosen locale in the cookie and redirects back to a same-host Referer (open-redirect safe). Point your language switcher at it:

```blade
@foreach (config('locale-cookie.supported') as $code)
    <a href="{{ route('locale.switch', ['locale' => $code]) }}">{{ strtoupper($code) }}</a>
@endforeach
```

The `{locale}` param is constrained to `config('locale-cookie.supported')`. Configure (or disable) it under `locale-cookie.switch`:

```php
'switch' => [
    'enabled' => true,
    'path' => 'locale/{locale}',
    'name' => 'locale.switch',
    'lifetime' => 60 * 24 * 365, // cookie lifetime in minutes
    'middleware' => ['web'],     // attaches the queued cookie; the locale cookie is excluded from encryption
],
```

### Shortening a locale code

The `LocaleCookie::short()` helper reduces a locale to its short language code — handy for `<html lang="…">`, flag icons, or any place you only need the base language:

```php
use JeffersonGoncalves\LocaleCookie\LocaleCookie;

LocaleCookie::short('pt_BR'); // 'pt'
LocaleCookie::short('pt-BR'); // 'pt'
LocaleCookie::short('EN');    // 'en'
LocaleCookie::short();        // current app locale, shortened
LocaleCookie::short('');      // 'en' (fallback when there is no usable prefix)
```

```blade
<html lang="{{ \JeffersonGoncalves\LocaleCookie\LocaleCookie::short() }}">
```

### URL-prefix mode (SEO / crawlable per-language URLs)

Cookie-only locale resolution is a dead end for SEO: crawlers never send a locale cookie, so they only ever see your fallback locale — there is no distinct, crawlable URL for a French version of a page to exist at, so there's nothing to point `hreflang` at.

Opt-in URL-prefix mode fixes that by giving every non-default locale a real path prefix (`/en/...`, `/fr/...`), while the default locale stays unprefixed at root (no mass redirect needed for your existing indexed URLs). Enable it in the config:

```php
'url_prefix' => [
    'enabled' => env('LOCALE_COOKIE_URL_PREFIX', false),
    'default_locale' => null, // falls back to `fallback` above when null
    'segments' => [
        'fr' => 'fr',
        'de' => 'de',
        'es' => 'es',
    ],
],
```

Wrap your route definitions in `LocaleCookie::routes()` instead of registering them directly. It registers each route once unprefixed for the default locale, and once more per configured segment — a Laravel route can't collapse a *leading* optional segment followed by more literal segments into both forms on its own, so this registers the group twice under the hood:

```php
use JeffersonGoncalves\LocaleCookie\LocaleCookie;

LocaleCookie::routes(function () {
    Route::middleware(['web', 'locale'])->get('projects', ProjectsController::class)->name('projects.show');
});
```

This yields `projects.show` (`/projects`, default locale) plus `fr.projects.show` (`/fr/projects`) and `es.projects.show` (`/es/projects`). With `url_prefix.enabled`, the `locale`/`SetLocale` middleware resolves the locale from the matched route's prefix instead of the cookie — the same URL always renders the same locale for anyone, bots included — and still keeps the cookie in sync for code that reads it directly.

Use `LocaleCookie::localizedRoute()` for a language switcher, so "this same page, in French" links to `/fr/projects` rather than swapping the cookie and reloading:

```php
LocaleCookie::localizedRoute('projects.show', [], 'fr'); // '.../fr/projects'
```

And `LocaleCookie::alternates()` to emit `hreflang` tags without reimplementing the locale-to-URL mapping yourself:

```blade
@foreach (\JeffersonGoncalves\LocaleCookie\LocaleCookie::alternates() as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach
```

#### Bringing your own dynamic `{locale}` routing

`LocaleCookie::routes()` isn't the only way to shape locale-prefixed routes — if your app registers every locale (default included) under a single dynamic group instead:

```php
Route::prefix('{locale}')->where('locale', 'en|fr|es')->group(function () {
    Route::middleware(['web', 'locale'])->get('projects', ProjectsController::class)->name('projects.show');
});
```

`SetLocale` still resolves it: with `url_prefix.enabled`, it checks the matched route's `locale` action first (for `LocaleCookie::routes()`'s groups), then falls back to a `{locale}` route parameter. Either source also fills `route()`/link generation with the resolved locale via `URL::defaults()`, so calls like `route('projects.show')` don't need `locale` passed explicitly.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jèfferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
