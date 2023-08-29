# Laravel Assistant

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mdhesari/laravel-assistant.svg?style=flat-square)](https://packagist.org/packages/mdhesari/laravel-assistant)
[![Total Downloads](https://img.shields.io/packagist/dt/mdhesari/laravel-assistant.svg?style=flat-square)](https://packagist.org/packages/mdhesari/laravel-assistant)

Larave assistant is a smart assistant tool for developers in order to develop and implement robust api for their client.

## Installation

You can install the package via composer:

```bash
composer require mdhesari/laravel-assistant --dev
```

## Usage

```shell
php artisan assistant:install
```

This package was created when I wanted to have a tool in order to scaffold my new modules in a second with my architecture and design concepts.

It's currently best fit for api design, for example I want to develop a Todo app.

before everything don't forget to add OPENAI_API_KEY in your .env.

```dotenv
OPENAI_API_KEY="YOUR_API_KEY"
```

The magical command assistant:crud uses base architecture for scaffolding model, controller, migration, request architecture, and also it creates some events and actions in order to integrate them together.

* Crud scaffolding

```shell
php artisan assistant:crud -a Task
```

* Migration

```shell
php artisan assistant:make-migration Task
```

* Model

```shell
php artisan assistant:make-model Task
```

* Request

```shell
php artisan assistant:make-request Task
```

* Modules

Use modules option in order to add files into their specified module.

```bash
--modules=true
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mdhesari99@gmail.com instead of using the issue tracker.

## Credits

-   [Mohamad Hesari](https://github.com/mdhesari)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
