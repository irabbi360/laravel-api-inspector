# Auto Generate API Documentation for request rules, parameters and API Response

[![Latest Version on Packagist](https://img.shields.io/packagist/v/irabbi360/laravel-api-inspector.svg?style=flat-square)](https://packagist.org/packages/irabbi360/laravel-api-inspector)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/irabbi360/laravel-api-inspector/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/irabbi360/laravel-api-inspector/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/irabbi360/laravel-api-inspector/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/irabbi360/laravel-api-inspector/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/irabbi360/laravel-api-inspector.svg?style=flat-square)](https://packagist.org/packages/irabbi360/laravel-api-inspector)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require irabbi360/laravel-api-inspector
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-api-inspector-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-api-inspector-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-api-inspector-views"
```

## Usage

```php
$laravelApiInspector = new Irabbi360\LaravelApiInspector();
echo $laravelApiInspector->echoPhrase('Hello, Irabbi360!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Fazle Rabbi](https://github.com/irabbi360)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
