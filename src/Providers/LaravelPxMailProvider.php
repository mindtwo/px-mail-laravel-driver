<?php

namespace mindtwo\LaravelPxMail\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use mindtwo\LaravelPxMail\Exceptions\InvalidConfigException;
use mindtwo\LaravelPxMail\Mailer\PxMailTransport;

class LaravelPxMailProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/px-mail.php' => config_path('px-mail.php'),
        ], 'px-mail');

        Mail::extend('txmail', function () {
            $stage = config('px-mail.stage');
            $mailerUrl = config('px-mail.mailer_url');
            $tenant = config('px-mail.tenant');
            $clientId = config('px-mail.client_id');
            $clientSecret = config('px-mail.client_secret');

            if (is_null($tenant) || is_null($clientId) || is_null($clientSecret)) {
                throw new InvalidConfigException('Missing required configuration for txmail: tenant, client_id, or client_secret.');
            }

            return new PxMailTransport(
                tenant: trim($tenant),
                clientId: trim($clientId),
                clientSecret: trim($clientSecret),
                stage: $stage,
                mailerUrl: $mailerUrl,
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/px-mail.php',
            'px-mail'
        );
    }
}
