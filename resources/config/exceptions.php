<?php

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
        \Illuminate\Validation\ValidationException::class => Formatters\ValidationExceptionFormatter::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class => Formatters\NotFoundHttpExceptionFormatter::class,
        \Illuminate\Auth\AuthenticationException::class => Formatters\AuthenticationExceptionFormatter::class,
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class => Formatters\AccessDeniedHttpExceptionFormatter::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class => Formatters\HttpExceptionFormatter::class,
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