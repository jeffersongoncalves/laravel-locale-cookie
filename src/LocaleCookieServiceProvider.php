<?php

namespace JeffersonGoncalves\LocaleCookie;

use Illuminate\Support\Facades\Route;
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
    }
}
