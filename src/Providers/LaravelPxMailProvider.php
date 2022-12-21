<?php

namespace mindtwo\LaravePxMail\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use mindtwo\LaravePxMail\Mailer\PxMailTransport;

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
            __DIR__.'/../config/px-mail.php' => config_path('px-mail.php'),
        ], 'px-mail');

        Mail::extend('txmail', function () {
            return new PxMailTransport(config('px-mail'));
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
            __DIR__.'/../config/px-mail.php',
            'px-mail'
        );
    }
}
