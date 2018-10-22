# iMemento Exception Handler for Laravel

This is a custom exception handler that must be registered with Laravel.

## Install
```bash
composer require imemento/exceptions-laravel
```

The exception to formatter mapping is done in the config/exceptions.php file.

Publish it if you want to add your custom mapping:
```bash
php artisan vendor:publish --tag=config
```

Replace the default exception handler in bootstrap/app.php
```php
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    iMemento\Exceptions\Laravel\ExceptionHandler::class
);
```
