<?php

namespace iMemento\Exceptions\Laravel;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use iMemento\Exceptions\Exception as CustomException;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    protected $exceptionsRendering = [
        'iMemento\Exceptions\ResourceException' => 'iMemento\Http\Responses\PreconditionFailedResponse',
        'Illuminate\Auth\AuthenticationException' => 'iMemento\Http\Responses\UnauthorizedResponse', //maybe best to handle in its method
        'iMemento\Exceptions\DeleteResourceFailedException' => 'iMemento\Http\Responses\PreconditionFailedResponse',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return mixed
     */
    public function render($request, Exception $e)
    {
        $e = $this->prepareException($e);
        $response = $this->tryPreconfiguredException($e);

        //if we matched something in $exceptionsRendering, return the response
        if($response)
            return $response;

        //TODO: handle differently for json and html

        //otherwise continue handling the exception
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        return $this->prepareResponse($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }


    /**
     * We try to return a response using the exceptionsRendering associations
     *
     * @param Exception $e
     * @return bool
     */
    protected function tryPreconfiguredException(Exception $e)
    {
        $debug = $this->buildDebugArray($e);
        $exceptionClass = get_class($e);

        //if we match something in exceptionsRendering, return the response
        if (!empty($this->exceptionsRendering[$exceptionClass])) {
            $responseClass = $this->exceptionsRendering[$exceptionClass];
            return new $responseClass(json_encode($debug));
        }

        return false;
    }


    /**
     * Creates the debug array
     *
     * @param $e
     * @return array
     */
    protected function buildDebugArray(Exception $e)
    {
        return [
            'id' => $e instanceof CustomException ? $e->getId() : null,
            'code' => $e->getCode(),
            'error' => $e->getMessage(),
            'debug' => $e instanceof CustomException ? $e->getDebug() : null,
        ];
    }


    /**
     * Prepare exception for rendering.
     *
     * @param  \Exception  $e
     * @return \Exception
     */
    /*protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage());
        }

        return $e;
    }*/
}