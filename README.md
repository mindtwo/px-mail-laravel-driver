# PX-Mail Laravel Driver

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

A Laravel mail driver for the PX Mail (TX Mail) API.

## Installation

```bash
composer require mindtwo/px-mail-laravel-driver
```

## Configuration

### Publish the config file

```bash
php artisan vendor:publish --tag=px-mail
```

This publishes `config/px-mail.php`. This step is optional if you configure everything via environment variables.

### Environment variables

Add the following to your `.env` file:

```dotenv
TX_MAIL_URL=https://tx-mail.api.pl-x.cloud
TX_MAIL_TENANT=your-tenant
TX_MAIL_CLIENT_ID=your-client-id
TX_MAIL_CLIENT_SECRET=your-client-secret
```

### Register the mailer

Add the transport to the `mailers` section in `config/mail.php`:

```php
'mailers' => [
    'txmail' => [
        'transport' => 'txmail',
    ],
],
```

Then set it as your default mailer in `.env`:

```dotenv
MAIL_MAILER=txmail
```

## Config reference

| Key | Env variable | Default | Description |
|---|---|---|---|
| `stage` | `APP_ENV` | `APP_ENV` | The application stage. `local` is mapped to `preprod` automatically. |
| `mailer_url` | `TX_MAIL_URL` | — | Base URL of the TX Mail API. |
| `mailer_api_version` | `TX_MAIL_API_VERSION` | `v1` | API version appended to the base URL. |
| `tenant` | `TX_MAIL_TENANT` | — | Your TX Mail tenant identifier used in the API path. |
| `client_id` | `TX_MAIL_CLIENT_ID` | — | Client ID for M2M authentication. |
| `client_secret` | `TX_MAIL_CLIENT_SECRET` | — | Client secret for M2M authentication. |
| `debug` | `TX_MAIL_DEBUG` | `false` | When `true`, enables HTTP-level request/response logging for all API calls. |
| `log_send` | `TX_MAIL_LOG_SEND` | `false` | When `true`, logs each outgoing send attempt (sender, recipient, tenant, URL). Can be enabled independently of `debug`. |
| `headers` | — | `[]` | Additional headers merged into every request. See [Additional headers](#additional-headers). |

## Usage

Once configured, use Laravel's `Mail` facade as normal:

```php
Mail::to($user)->send(new OrderConfirmation($order));
```

## Context headers

The driver supports optional `x-context-tenant-code` and `x-context-domain-code` headers. These are useful in multi-tenant applications where the sending context needs to be forwarded to the mail API.

Set them on the `ApiClient` via the service container's `resolving` callback, typically inside a service provider:

```php
use mindtwo\LaravelPxMail\Client\ApiClient;

$this->app->resolving(ApiClient::class, function (ApiClient $client) {
    $client->setContextTenant(tenant()->code);
    $client->setContextDomain(domain()->code);
});
```

The callbacks are resolved fresh on every `ApiClient` instantiation, so the values are always up to date.

You can also set them directly on the resolved instance at any point before sending:

```php
app(ApiClient::class)
    ->setContextTenant('acme')
    ->setContextDomain('shop');
```

## Additional headers

Static additional headers can be defined in the config:

```php
// config/px-mail.php
'headers' => [
    'X-Custom-Header' => 'value',
],
```

They can also be set at runtime, for example in middleware:

```php
config(['px-mail.headers' => ['X-Custom-Header' => 'value']]);
```

## Logging

### Send logging

Enable send logging to record every outgoing mail attempt:

```dotenv
TX_MAIL_LOG_SEND=true
```

Logs the tenant, client ID, API URL, sender address, and anonymized recipient (e.g. `j***@example.com`) before each send. All entries are prefixed with `[px-mail]`.

### Debug mode

Enable debug mode for HTTP-level logging:

```dotenv
TX_MAIL_DEBUG=true
```

Logs the full request and response details for every API call. Errors (4xx/5xx) are always logged regardless of this setting.

Both options can be combined or used independently.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@mindtwo.de instead of using the issue tracker.

## Credits

- [mindtwo GmbH][link-author]
- [All Other Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mindtwo/px-mail-laravel-driver.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mindtwo/px-mail-laravel-driver.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/mindtwo/px-mail-laravel-driver
[link-downloads]: https://packagist.org/packages/mindtwo/px-mail-laravel-driver
[link-author]: https://github.com/mindtwo
[link-contributors]: ../../contributors
