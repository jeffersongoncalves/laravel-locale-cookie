<?php

namespace JeffersonGoncalves\LocaleCookie\Tests;

use JeffersonGoncalves\LocaleCookie\LocaleCookieServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LocaleCookieServiceProvider::class,
        ];
    }
}
