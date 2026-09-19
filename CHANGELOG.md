# Changelog

All notable changes to `laravel-locale-cookie` will be documented in this file.

## v1.5.0 - 2026-09-19

### What's Changed

* feat: fall back to a dynamic {locale} route parameter in url-prefix mode by @jeffersongoncalves in https://github.com/jeffersongoncalves/laravel-locale-cookie/pull/8

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-locale-cookie/compare/v1.4.0...v1.5.0

## v1.4.0 - 2026-09-14

### What's Changed

- feat: add opt-in URL-prefix locale mode for SEO (#6) — non-default locales get a real path prefix (`/fr/...`), default locale stays unprefixed at root. Adds `LocaleCookie::localizedRoute()`, `LocaleCookie::alternates()` for hreflang, and route-prefix resolution in `SetLocale` middleware.

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-locale-cookie/compare/v1.3.0...v1.4.0

## v1.3.0 - 2026-06-21

Add `LocaleCookie::short()` locale → language-code helper.

## v1.2.0 - 2026-06-21

Add a config-driven locale switch route + SwitchLocaleController (`locale-cookie.switch`).

## v1.1.0 - 2026-06-21

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-locale-cookie/compare/v1.0.1...v1.1.0

## v1.0.1 - 2026-06-20

chore: ignore the .phpunit.cache directory.

## v1.0.0 - 2026-06-20

Initial release.
