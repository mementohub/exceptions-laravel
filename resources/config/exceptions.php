<?php

use Symfony\Component\HttpKernel\Exception as SymfonyException;
use iMemento\Exceptions\Laravel\Formatters;

return [

    /*
    |--------------------------------------------------------------------------
    | Exception to Response mapping
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default mapping of exceptions to responses.
    |
    */

    'formatters' => [
        SymfonyException\UnprocessableEntityHttpException::class => Formatters\UnprocessableEntityHttpExceptionFormatter::class,
        SymfonyException\HttpException::class => Formatters\HttpExceptionFormatter::class,
        Exception::class => Formatters\ExceptionFormatter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exceptions that shouldn't be reported
    |--------------------------------------------------------------------------
    |
    | Here you may specify the errors that you don't want reported.
    |
    */

    'dont_report' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Http\Exceptions\HttpResponseException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ],

    'dont_flash' => [
        'password',
        'password_confirmation',
    ]

];