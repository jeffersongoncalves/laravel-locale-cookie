<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LocaleCookie\LocaleCookie;

function enableUrlPrefix(array $segments = ['fr' => 'fr', 'es' => 'es'], ?string $default = 'en'): void
{
    test()->envConfig = [
        'locale-cookie.supported' => array_merge([$default], array_keys($segments)),
        'locale-cookie.fallback' => $default,
        'locale-cookie.url_prefix.enabled' => true,
        'locale-cookie.url_prefix.default_locale' => $default,
        'locale-cookie.url_prefix.segments' => $segments,
    ];
    test()->refreshApplication();
}

function registerProjectRoute(): void
{
    LocaleCookie::routes(function () {
        Route::middleware(['web', 'locale'])
            ->get('projects', fn () => App::getLocale())
            ->name('projects.show');
    });
}

it('does not duplicate routes when url-prefix mode is disabled', function () {
    LocaleCookie::routes(function () {
        Route::get('projects', fn () => 'ok')->name('projects.show');
    });

    expect(route('projects.show'))->toBe(url('/projects'));
    $this->get('/en/projects')->assertNotFound();
});

it('registers the default locale unprefixed and other locales under their segment', function () {
    enableUrlPrefix();
    registerProjectRoute();

    $this->get('/projects')->assertOk()->assertSee('en');
    $this->get('/fr/projects')->assertOk()->assertSee('fr');
    $this->get('/es/projects')->assertOk()->assertSee('es');
});

it('resolves the route-derived locale over a conflicting cookie', function () {
    enableUrlPrefix();
    registerProjectRoute();

    $this->withUnencryptedCookie('locale', 'es')
        ->get('/fr/projects')
        ->assertOk()
        ->assertSee('fr');
});

it('generates a route-compatible localized URL for the switcher', function () {
    enableUrlPrefix();
    registerProjectRoute();

    expect(LocaleCookie::localizedRoute('projects.show', [], 'en'))->toBe(url('/projects'));
    expect(LocaleCookie::localizedRoute('projects.show', [], 'fr'))->toBe(url('/fr/projects'));
    expect(LocaleCookie::localizedRoute('fr.projects.show', [], 'es'))->toBe(url('/es/projects'));
});

it('exposes hreflang alternates for the current route', function () {
    enableUrlPrefix();
    registerProjectRoute();

    $this->get('/fr/projects');

    expect(LocaleCookie::alternates())->toBe([
        'en' => url('/projects'),
        'fr' => url('/fr/projects'),
        'es' => url('/es/projects'),
    ]);
});

it('returns no alternates when url-prefix mode is disabled', function () {
    registerProjectRoute();

    $this->get('/projects');

    expect(LocaleCookie::alternates())->toBe([]);
});
