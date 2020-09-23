# iMemento Exception Handler for Laravel
[![Build Status](https://travis-ci.org/mementohub/exceptions-laravel.svg?branch=master)](https://travis-ci.org/mementohub/exceptions-laravel)
[![Latest Stable Version](https://poser.pugx.org/imemento/exceptions-laravel/v/stable)](https://packagist.org/packages/imemento/exceptions-laravel)
[![License](https://poser.pugx.org/imemento/exceptions-laravel/license)](https://packagist.org/packages/imemento/exceptions-laravel)
[![Total Downloads](https://poser.pugx.org/imemento/exceptions-laravel/downloads)](https://packagist.org/packages/imemento/exceptions-laravel)

This is a custom exception handler that must be registered with Laravel.

## Install
```bash
composer require imemento/exceptions-laravel
```

Add the service to config/app.php:
```php
iMemento\Exceptions\Laravel\ExceptionsServiceProvider::class,
```

The exception to formatter mapping is done in the config/exceptions.php file.

Publish it if you want to add your custom mapping:
```bash
php artisan vendor:publish --tag=config
```

Replace the exception handler in bootstrap/app.php
```php
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    iMemento\Exceptions\Laravel\ExceptionHandler::class
);
```
