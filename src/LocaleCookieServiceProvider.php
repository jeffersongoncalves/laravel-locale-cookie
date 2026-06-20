<?php

namespace JeffersonGoncalves\LocaleCookie;

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
}
