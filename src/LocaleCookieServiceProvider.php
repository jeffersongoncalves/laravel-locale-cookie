<?php

namespace JeffersonGoncalves\LocaleCookie;

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
        Route::aliasMiddleware('locale', SetLocale::class);

        if (config('locale-cookie.switch.enabled', true)) {
            Route::get((string) config('locale-cookie.switch.path', 'locale/{locale}'), SwitchLocaleController::class)
                ->middleware((array) config('locale-cookie.switch.middleware', []))
                ->whereIn('locale', (array) config('locale-cookie.supported', []))
                ->name((string) config('locale-cookie.switch.name', 'locale.switch'));
        }
    }
}
