<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;

function handleWithCookies(array $cookies = []): string
{
    $request = Request::create('/', 'GET', [], $cookies);

    (new SetLocale)->handle($request, fn ($request) => response('ok'));

    return App::getLocale();
}

it('sets the app locale from a supported locale cookie', function () {
    config()->set('locale-cookie.cookie', 'locale');
    config()->set('locale-cookie.supported', ['en', 'pt_BR', 'es']);
    config()->set('locale-cookie.fallback', 'en');

    expect(handleWithCookies(['locale' => 'pt_BR']))->toBe('pt_BR');
});

it('falls back when the cookie holds an unsupported locale', function () {
    config()->set('locale-cookie.cookie', 'locale');
    config()->set('locale-cookie.supported', ['en']);
    config()->set('locale-cookie.fallback', 'en');

    expect(handleWithCookies(['locale' => 'xx']))->toBe('en');
});

it('uses the fallback locale when no cookie is present', function () {
    config()->set('locale-cookie.cookie', 'locale');
    config()->set('locale-cookie.supported', ['en', 'pt_BR']);
    config()->set('locale-cookie.fallback', 'pt_BR');

    expect(handleWithCookies())->toBe('pt_BR');
});

it('falls back to app.fallback_locale when no package fallback is configured', function () {
    config()->set('locale-cookie.cookie', 'locale');
    config()->set('locale-cookie.supported', ['en']);
    config()->set('locale-cookie.fallback', null);
    config()->set('app.fallback_locale', 'en');

    expect(handleWithCookies(['locale' => 'unknown']))->toBe('en');
});

it('respects a custom cookie name from config', function () {
    config()->set('locale-cookie.cookie', 'lang');
    config()->set('locale-cookie.supported', ['en', 'es']);
    config()->set('locale-cookie.fallback', 'en');

    expect(handleWithCookies(['lang' => 'es']))->toBe('es');
});

it('applies the locale over the HTTP stack via the registered `locale` middleware alias', function () {
    // Exercises the real EncryptCookies -> SetLocale path: because the cookie
    // is excluded from encryption, a raw client-set cookie survives the web
    // group and the `locale` alias resolves it.
    $this->envConfig = ['locale-cookie.supported' => ['en', 'pt_BR']];
    $this->refreshApplication();

    Route::middleware(['web', 'locale'])->get('/lang', fn () => app()->getLocale());

    $this->withUnencryptedCookie('locale', 'pt_BR')
        ->get('/lang')
        ->assertOk()
        ->assertSee('pt_BR');
});

it('falls back to en when neither package nor app fallback is configured', function () {
    config()->set('locale-cookie.cookie', 'locale');
    config()->set('locale-cookie.supported', ['en']);
    config()->set('locale-cookie.fallback', null);
    config()->set('app.fallback_locale', null);

    expect(handleWithCookies())->toBe('en');
});
