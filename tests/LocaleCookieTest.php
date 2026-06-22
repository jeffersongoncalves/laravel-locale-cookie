<?php

use Illuminate\Support\Facades\App;
use JeffersonGoncalves\LocaleCookie\LocaleCookie;

it('shortens a region locale to its language code', function () {
    expect(LocaleCookie::short('pt_BR'))->toBe('pt');
    expect(LocaleCookie::short('pt-BR'))->toBe('pt');
    expect(LocaleCookie::short('EN'))->toBe('en');
});

it('defaults to the application locale', function () {
    App::setLocale('es_ES');

    expect(LocaleCookie::short())->toBe('es');
});

it('falls back to en for an empty locale', function () {
    expect(LocaleCookie::short(''))->toBe('en');
});
