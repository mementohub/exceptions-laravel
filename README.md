# iMemento Exception Handler for Laravel

This is a custom exception handler that must be registered with Laravel.

## Install
```bash
composer require imemento/exceptions-laravel
```

## Usage
Edit app/Providers/AppServiceProvider.php and add:
```php
use Illuminate\Contracts\Debug\ExceptionHandler as AbstractHandler;
use iMemento\Exceptions\Laravel\ExceptionHandler;

...

//add this to the boot method
$this->app->bind(
	AbstractHandler::class,
	ExceptionHandler::class
);
```

## Mapping
The exception to response mapping is done in the $exceptionsRendering property on the handler.

//TODO move this mapping to a config file
