<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exception to Response mapping
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default mapping of exceptions to responses.
    |
    */

    'mapping' => [
        'iMemento\Exceptions\InvalidTokenException' => 'iMemento\Http\Responses\UnauthorizedResponse',
        'iMemento\Exceptions\MissingTokenException' => 'iMemento\Http\Responses\UnauthorizedResponse',
        'iMemento\Exceptions\ExpiredConsumerTokenException' => 'iMemento\Http\Responses\UnauthorizedResponse',
        'iMemento\Exceptions\ExpiredAuthTokenException' => 'iMemento\Http\Responses\UnauthorizedResponse',
        'iMemento\Exceptions\InvalidPermissionsException' => 'iMemento\Http\Responses\UnauthorizedResponse',

        'iMemento\Exceptions\ResourceException' => 'iMemento\Http\Responses\PreconditionFailedResponse',
        'Illuminate\Auth\AuthenticationException' => 'iMemento\Http\Responses\UnauthorizedResponse', //maybe best to handle in its method
        'iMemento\Exceptions\DeleteResourceFailedException' => 'iMemento\Http\Responses\PreconditionFailedResponse',
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
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ],

];