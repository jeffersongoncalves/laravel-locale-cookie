<?php

declare(strict_types=1);

namespace JeffersonGoncalves\LocaleCookie;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocaleCookie
{
    /**
     * Reduce a locale to its short language code (`pt_BR`/`pt-BR`/`pt` → `pt`).
     * Defaults to the application's current locale. Falls back to `en` when the
     * locale has no usable prefix.
     */
    public static function short(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        $base = strtolower(preg_split('/[_-]/', $locale)[0] ?? '');

        return $base !== '' ? $base : 'en';
    }

    public static function urlPrefixEnabled(): bool
    {
        return (bool) config('locale-cookie.url_prefix.enabled', false);
    }

    /**
     * The locale that stays unprefixed at the URL root.
     */
    public static function defaultLocale(): string
    {
        return (string) (
            config('locale-cookie.url_prefix.default_locale')
            ?? config('locale-cookie.fallback')
            ?? config('app.fallback_locale')
            ?? 'en'
        );
    }

    /**
     * @return array<string, string> locale => URL segment, non-default locales only.
     */
    public static function segments(): array
    {
        return (array) config('locale-cookie.url_prefix.segments', []);
    }

    /**
     * Register `$callback`'s route definitions once unprefixed for the
     * default locale, and once more per `url_prefix.segments` entry under a
     * `/{segment}` prefix group, so a single route file yields a crawlable
     * URL per locale. No-ops straight to `$callback()` when url-prefix mode
     * is disabled, so this is safe to call unconditionally.
     *
     * Each non-default group's route names are prefixed with `{locale}.`
     * (via the `as` group attribute) to avoid name collisions with the
     * default group — use `LocaleCookie::localizedRoute()` to resolve the
     * right name for a given locale instead of guessing the prefix yourself.
     */
    public static function routes(Closure $callback): void
    {
        if (! static::urlPrefixEnabled()) {
            $callback();

            return;
        }

        Route::group(['locale' => static::defaultLocale()], $callback);

        foreach (static::segments() as $locale => $segment) {
            Route::group([
                'prefix' => $segment,
                'as' => $locale.'.',
                'locale' => $locale,
            ], $callback);
        }
    }

    /**
     * The correctly-prefixed URL for `$name` (as registered inside
     * `LocaleCookie::routes()`, unprefixed) in `$locale` — e.g. a language
     * switcher linking "this same page, in French" to `/fr/current-page`.
     * Defaults to the application's current locale.
     */
    public static function localizedRoute(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= App::getLocale();
        $base = self::stripLocalePrefix($name);

        if ($locale === static::defaultLocale()) {
            return route($base, $parameters);
        }

        return route($locale.'.'.$base, $parameters);
    }

    /**
     * "This same route, in every configured locale" — locale => URL, for a
     * consuming app's `<head>` to emit `<link rel="alternate" hreflang="...">`
     * tags without reimplementing the locale-to-URL mapping. Defaults to the
     * currently matched route. Returns an empty array when url-prefix mode is
     * disabled or there is no named current route to resolve from.
     *
     * @return array<string, string> locale => URL
     */
    public static function alternates(?string $name = null, array $parameters = []): array
    {
        if (! static::urlPrefixEnabled()) {
            return [];
        }

        $route = request()->route();
        $name ??= is_object($route) ? $route->getName() : null;

        if ($name === null) {
            return [];
        }

        $base = self::stripLocalePrefix($name);
        $parameters = $parameters ?: (is_object($route) ? $route->parameters() : []);

        $alternates = [static::defaultLocale() => route($base, $parameters)];

        foreach (array_keys(static::segments()) as $locale) {
            $alternates[$locale] = route($locale.'.'.$base, $parameters);
        }

        return $alternates;
    }

    /**
     * Strip a leading `{locale}.` prefix (added by `routes()`'s non-default
     * groups) from a route name, so callers can pass either the base name or
     * an already-localized one interchangeably.
     */
    private static function stripLocalePrefix(string $name): string
    {
        foreach (array_keys(static::segments()) as $locale) {
            if (str_starts_with($name, $locale.'.')) {
                return substr($name, strlen($locale) + 1);
            }
        }

        return $name;
    }
}
