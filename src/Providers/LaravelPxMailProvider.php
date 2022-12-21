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
        Mail::extend('txmail', function (array $config = []) {
            return new PxMailTransport($config);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
