<?php

namespace MASNathan\LaravelApiAuth;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->make('router')->aliasMiddleware('api.auth', AuthenticationMiddleware::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/api_auth.php', 'api_auth');
    }
}
