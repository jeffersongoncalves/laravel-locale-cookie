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

    protected function getEnvironmentSetUp($app): void
    {
        // The switch route runs in the `web` group (EncryptCookies), so an app
        // key is required to encrypt the queued locale cookie.
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
