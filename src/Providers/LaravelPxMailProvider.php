<?php declare(strict_types=1);

namespace mindtwo\LaravelPxMail\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use mindtwo\LaravelPxMail\Client\ApiClient;
use mindtwo\LaravelPxMail\Exceptions\InvalidConfigException;
use mindtwo\LaravelPxMail\Mailer\PxMailTransport;

class LaravelPxMailProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/px-mail.php' => config_path('px-mail.php'),
        ], 'px-mail');

        $this->app->bind(ApiClient::class, function ($app) {
            $tenant = config('px-mail.tenant');
            $clientId = config('px-mail.client_id');
            $clientSecret = config('px-mail.client_secret');

            if ($tenant === null || $clientId === null || $clientSecret === null) {
                throw new InvalidConfigException(
                    'Missing required configuration for txmail: tenant, client_id, or client_secret.',
                );
            }

            $stage = config('px-mail.stage');
            $mailerUrl = config('px-mail.mailer_url');
            $mailerApiVersion = config('px-mail.mailer_api_version');

            return new ApiClient(
                tenant: mb_trim($tenant),
                clientId: mb_trim($clientId),
                clientSecret: mb_trim($clientSecret),
                stage: $stage,
                mailerUrl: $mailerUrl,
                mailerApiVersion: $mailerApiVersion,
                debug: config('px-mail.debug', false),
            );
        });

        Mail::extend('txmail', fn () => new PxMailTransport(client: app(ApiClient::class)));
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/px-mail.php', 'px-mail');
    }
}
