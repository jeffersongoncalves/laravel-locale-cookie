---
name: locale-cookie-development
description: Development guide for laravel-locale-cookie, a package that resolves the application locale from a cookie, validating it against a configurable list of supported locales with a fallback.
---

# Locale Cookie Development Skill

## When to use this skill

- When developing or extending the laravel-locale-cookie package
- When changing how the locale cookie is read or validated
- When adjusting the config schema (cookie name, supported locales, fallback)
- When writing tests for locale resolution
- When debugging why a locale is or is not being applied for a request

## Setup

### Requirements
- PHP 8.2+
- Laravel 11, 12, or 13
- `spatie/laravel-package-tools` ^1.14

### Installation

```bash
composer require jeffersongoncalves/laravel-locale-cookie
```

Publish the config file:

```bash
php artisan vendor:publish --tag="locale-cookie-config"
```

## Package Structure

```
src/
  LocaleCookieServiceProvider.php   # Registers + merges the config file
  Middleware/
    SetLocale.php                   # Reads the cookie, validates it, sets the locale
config/
  locale-cookie.php                 # cookie name, supported locales, fallback
```

## Configuration

```php
// config/locale-cookie.php
return [
    'cookie' => 'locale',      // cookie name to read
    'supported' => ['en'],     // accepted locales (whitelist)
    'fallback' => null,        // null => config('app.fallback_locale')
];
```

- `cookie` — name of the cookie the middleware reads.
- `supported` — whitelist of locales the app accepts; any value not in the list is ignored.
- `fallback` — locale used when the cookie is missing or unsupported. `null` defers to `config('app.fallback_locale')`.

## How It Works

The `SetLocale` middleware:

1. Reads the cookie named by `config('locale-cookie.cookie')`.
2. Resolves the fallback as `config('locale-cookie.fallback') ?? config('app.fallback_locale')`.
3. If the cookie value is not a string or is not in `config('locale-cookie.supported')`, it uses the fallback.
4. Calls `App::setLocale($locale)` and passes the request along.

```php
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
```

## Registering the Middleware

Register it on the route group that should be locale-aware:

```php
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

Route::middleware(SetLocale::class)->group(function () {
    // ...locale-aware routes
});
```

### Livewire

If the locale-aware pages use Livewire, also register the middleware as a persistent middleware so `/livewire/update` requests keep the visitor's locale instead of falling back to the default:

```php
use Livewire\Livewire;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

Livewire::addPersistentMiddleware([
    SetLocale::class,
]);
```

## Testing Patterns

Build a `Request` carrying the cookie, run the middleware, and assert `App::getLocale()`:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

function handleWithCookies(array $cookies = []): string
{
    $request = Request::create('/', 'GET', [], $cookies);

    (new SetLocale)->handle($request, fn ($request) => response('ok'));

    return App::getLocale();
}

it('sets the app locale from a supported locale cookie', function () {
    config()->set('locale-cookie.supported', ['en', 'pt_BR']);
    config()->set('locale-cookie.fallback', 'en');

    expect(handleWithCookies(['locale' => 'pt_BR']))->toBe('pt_BR');
});

it('falls back when the cookie holds an unsupported locale', function () {
    config()->set('locale-cookie.supported', ['en']);
    config()->set('locale-cookie.fallback', 'en');

    expect(handleWithCookies(['locale' => 'xx']))->toBe('en');
});
```

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage

# Static analysis
vendor/bin/phpstan analyse

# Code formatting
vendor/bin/pint
```

## Extending

To support an additional resolution source (e.g. a query string override), keep the validation against `config('locale-cookie.supported')` so unknown values are always ignored. Never call `App::setLocale()` with an unvalidated value.
