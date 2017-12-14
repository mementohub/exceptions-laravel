<?php

namespace iMemento\Exceptions\Laravel;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use iMemento\Exceptions\Exception as CustomException;

/**
 * Class ExceptionHandler
 *
 * @package iMemento\Exceptions\Laravel
 */
class ExceptionHandler extends Handler
{

    /**
     * @var array
     */
    protected $dontReport;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * ExceptionHandler constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->dontReport = config('exceptions.dont_report');
        $this->mapping = config('exceptions.mapping');

        parent::__construct($container);
    }

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
        //$e = $this->prepareException($e); //todo check if this is actually needed
        $response = $this->tryPreconfiguredException($e);

        //if we matched something in $exceptionsRendering, return the response
        if($response && $request->expectsJson()) {
            return $response;
        } elseif ($e instanceof AuthorizationException || $e instanceof AuthenticationException) {
            return redirect()->guest(route('login'));
        }

        //otherwise continue handling the exception
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }/* elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        }*/ elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        return $request->expectsJson() ? $this->prepareJsonResponse($request, $e) : $this->prepareResponse($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    /*protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }*/

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
        if (!empty($this->mapping[$exceptionClass])) {
            $responseClass = $this->mapping[$exceptionClass];
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

}