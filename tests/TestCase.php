<?php declare(strict_types=1);

namespace Tests;

use mindtwo\LaravelPxMail\Providers\LaravelPxMailProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelPxMailProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default environment configuration here
    }
}
