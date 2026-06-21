<div class="filament-hidden">

![Laravel Locale Cookie](https://raw.githubusercontent.com/jeffersongoncalves/laravel-locale-cookie/master/art/jeffersongoncalves-laravel-locale-cookie.png)

</div>

# Laravel Locale Cookie

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

By default Laravel's `EncryptCookies` middleware encrypts every cookie, and it runs **before** this middleware. To make sure the value is read correctly — and so that **client-set** cookies (e.g. the JavaScript example above, which are never encrypted) keep working — this package reads the raw cookie value via `$request->cookies->get()`, bypassing decryption.

If you would rather have Laravel manage the cookie as an encrypted value set from the server, exclude it from encryption instead by adding the cookie name to the `EncryptCookies` `$except` array:

```php
// app/Http/Middleware/EncryptCookies.php
protected $except = [
    'locale',
];
```

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
