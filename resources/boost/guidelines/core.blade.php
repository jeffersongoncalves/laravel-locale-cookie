## Laravel Locale Cookie

### Overview
Laravel Locale Cookie provides a middleware that resolves the application locale from a cookie. The cookie value is validated against a configurable whitelist of supported locales; unknown or missing values are ignored in favour of a fallback locale.

**Namespace:** `JeffersonGoncalves\LocaleCookie`
**Service Provider:** `LocaleCookieServiceProvider` (auto-discovered)
**Middleware:** `JeffersonGoncalves\LocaleCookie\Middleware\SetLocale`

### Key Concepts
- **Cookie-driven:** The locale is read from a cookie (default name `locale`).
- **Validated:** The cookie value must be present in the configured `supported` list, otherwise it is ignored.
- **Fallback:** When the cookie is missing or unsupported, the `fallback` locale is used (or `config('app.fallback_locale')` when `fallback` is null).

### Configuration

@verbatim
<code-snippet name="config" lang="php">
// config/locale-cookie.php
return [
    'cookie' => 'locale',      // cookie name to read
    'supported' => ['en'],     // accepted locales (whitelist)
    'fallback' => null,        // null => config('app.fallback_locale')
];
</code-snippet>
@endverbatim

### Registering the Middleware

@verbatim
<code-snippet name="middleware-registration" lang="php">
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

Route::middleware(SetLocale::class)->group(function () {
    // ...locale-aware routes
});
</code-snippet>
@endverbatim

### Using with Livewire

@verbatim
<code-snippet name="livewire-persistent" lang="php">
use Livewire\Livewire;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

// Register both as route-group middleware AND as Livewire persistent
// middleware so /livewire/update requests keep the visitor's locale.
Livewire::addPersistentMiddleware([
    SetLocale::class,
]);
</code-snippet>
@endverbatim

### Conventions
- The middleware lives in the `JeffersonGoncalves\LocaleCookie\Middleware` namespace.
- Cookie name, supported locales, and fallback are all resolved from `config('locale-cookie.*')`.
- An unsupported cookie value never sets the locale — it always resolves to the fallback.
