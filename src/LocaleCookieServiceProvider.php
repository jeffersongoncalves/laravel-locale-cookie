<?php

declare(strict_types=1);

namespace JeffersonGoncalves\LocaleCookie;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LocaleCookie\Http\Controllers\SwitchLocaleController;
use JeffersonGoncalves\LocaleCookie\Middleware\SetLocale;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LocaleCookieServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-locale-cookie')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        // The locale cookie is read straight from the raw Symfony cookie bag
        // (so a JavaScript-set value still works), so it must never be
        // encrypted on the way out either. Excluding it from EncryptCookies
        // keeps the write side (SwitchLocaleController) and the read side
        // (SetLocale) consistent — otherwise the cookie would be queued
        // encrypted under the `web` group and the middleware would read an
        // encrypted blob, so the chosen locale would never apply.
        EncryptCookies::except((string) config('locale-cookie.cookie', 'locale'));

        Route::aliasMiddleware('locale', SetLocale::class);

        if (config('locale-cookie.switch.enabled', true)) {
            Route::get((string) config('locale-cookie.switch.path', 'locale/{locale}'), SwitchLocaleController::class)
                ->middleware((array) config('locale-cookie.switch.middleware', []))
                ->whereIn('locale', (array) config('locale-cookie.supported', []))
                ->name((string) config('locale-cookie.switch.name', 'locale.switch'));
        }
    }
}
