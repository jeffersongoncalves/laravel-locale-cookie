<?php

namespace JeffersonGoncalves\LocaleCookie\Tests;

use JeffersonGoncalves\LocaleCookie\LocaleCookieServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Extra config applied on (re)boot. Set this then call
     * $this->refreshApplication() to exercise the provider with config that
     * must be present *before* the service provider registers its routes.
     *
     * @var array<string, mixed>
     */
    public array $envConfig = [];

    protected function getPackageProviders($app)
    {
        return [
            LocaleCookieServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // The switch route runs in the `web` group (EncryptCookies), so an app
        // key is required to encrypt the other (session/CSRF) cookies.
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        foreach ($this->envConfig as $key => $value) {
            $app['config']->set($key, $value);
        }
    }
}
