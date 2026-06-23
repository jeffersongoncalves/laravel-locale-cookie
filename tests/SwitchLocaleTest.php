<?php

use Illuminate\Support\Facades\Route;

it('persists the chosen locale cookie and returns to a same-host referer', function () {
    $this->withHeaders(['referer' => url('/projects')])
        ->get('/locale/en')
        ->assertRedirect(url('/projects'))
        // The locale cookie is excluded from encryption, so it is stored raw.
        ->assertCookie('locale', 'en', false);
});

it('ignores a cross-origin referer and falls back home (open-redirect guard)', function () {
    $this->withHeaders(['referer' => 'https://evil.example.com/phish'])
        ->get('/locale/en')
        ->assertRedirect('/');
});

it('falls back home when no referer is present', function () {
    $this->get('/locale/en')
        ->assertRedirect('/')
        ->assertCookie('locale', 'en', false);
});

it('rejects an unsupported locale at the route level', function () {
    $this->get('/locale/de')->assertNotFound();
});

it('round-trips: a switched locale is applied on the next request', function () {
    $this->envConfig = ['locale-cookie.supported' => ['en', 'pt']];
    $this->refreshApplication();

    Route::middleware(['web', 'locale'])->get('/current-locale', fn () => app()->getLocale());

    // Switch to pt through the web group (EncryptCookies included) and confirm
    // the cookie is written raw (not an encrypted blob).
    $switch = $this->withHeaders(['referer' => url('/home')])->get('/locale/pt');
    $switch->assertCookie('locale', 'pt', false);
    expect($switch->getCookie('locale', false)->getValue())->toBe('pt');

    // Send that exact cookie back: the middleware must apply the locale.
    $this->withUnencryptedCookie('locale', 'pt')
        ->get('/current-locale')
        ->assertOk()
        ->assertSee('pt');
});

it('does not register the switch route when switching is disabled', function () {
    $this->envConfig = ['locale-cookie.switch.enabled' => false];
    $this->refreshApplication();

    $this->get('/locale/en')->assertNotFound();
});

it('registers the switch route at a custom path and name', function () {
    $this->envConfig = [
        'locale-cookie.supported' => ['en', 'pt'],
        'locale-cookie.switch.path' => 'change-language/{locale}',
        'locale-cookie.switch.name' => 'lang.switch',
    ];
    $this->refreshApplication();

    expect(route('lang.switch', ['locale' => 'pt']))->toBe(url('/change-language/pt'));

    $this->withHeaders(['referer' => url('/home')])
        ->get('/change-language/pt')
        ->assertRedirect(url('/home'))
        ->assertCookie('locale', 'pt', false);

    // The default path is no longer registered under the custom config.
    $this->get('/locale/pt')->assertNotFound();
});
