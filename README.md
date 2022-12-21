# Laravel PX-User Package

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

## Installation

You can install the package via composer:

```bash
composer require mindtwo/px-mail-laravel-driver
```

## How to use?

### Publish config

To publish the modules config file simply run

```bash
php artisan vendor:publish px-mail
```
This publishes the `px-mail.php` config file to your projects config folder. Note: This step is optional as long as you complete the next step.

### Configure the package

After that you should add the following keys to your .env-file:

- TX_MAIL_TENANT
- TX_MAIL_CLIENT_ID
- TX_MAIL_CLIENT_SECRET

This keys will auto populate the respective config values.

Inside your configuration you will also find the key:

`stage` which will use your APP_ENV variable.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@mindtwo.de instead of using the issue tracker.

## Credits

- [mindtwo GmbH][link-author]
- [All Other Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mindtwo/px-user-laravel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mindtwo/px-user-laravel.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/mindtwo/px-user-laravel
[link-downloads]: https://packagist.org/packages/mindtwo/px-user-laravel
[link-author]: https://github.com/mindtwo
[link-contributors]: ../../contributors
