<?php

namespace iMemento\Exceptions\Laravel;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as LaravelHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandler extends LaravelHandler
{
    protected $config;
    protected $debug;

    /**
     * ExceptionHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->config = $container['config']->get('exceptions');

        $this->dontReport = $this->config['dont_report'];
        $this->dontFlash = $this->config['dont_flash'];
    }


    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return mixed
     *
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        //add an unique id before reporting and rendering anything
        $e->id = Str::random(32);

        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            return $e->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error(
            $e->getMessage() . " [$e->id]",
            array_merge($this->context(), ['exception' => $e]
            ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Exception                $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);

        //todo check if wants json here and go through formatter


        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof AccessDeniedHttpException) {
            return $this->unauthorized($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        return ! $request->expectsJson()
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(route('login'));
    }

    /**
     * Convert an authorization exception into a response.
     *
     * @param \Illuminate\Http\Request  $request
     * @param AccessDeniedHttpException $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function unauthorized($request, AccessDeniedHttpException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 403)
            : redirect()->guest(route('login'));
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Exception  $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        $r = config('app.debug') ? [
            'id' => $e->id,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'id' => $e->id,
            'code' => $e->getCode(),
            'message' => $this->isHttpException($e) || $this->isValidationException($e) ? $e->getMessage() : 'Server Error',
        ];

        if ($this->isValidationException($e))
            $r = $this->formatValidationErrors($r, $e);

        if ($this->isNotFoundException($e))
            $r = $this->formatNotFound($r, $e);

        return $r;
    }

    /**
     * Append the validation errors and code to the exception array
     *
     * @param array               $r
     * @param ValidationException $e
     * @return array
     */
    protected function formatValidationErrors(array $r, ValidationException $e)
    {
        $r['errors'] = [];
        $r['code'] = $e->status;

        foreach ($e->errors() as $k => $v) {
            foreach ($v as $m) {
                array_push($r['errors'], [
                    'input' => $k,
                    'message' => $m,
                ]);
            }
        }

        return $r;
    }

    /**
     * @param array                 $r
     * @param NotFoundHttpException $e
     * @return array
     */
    protected function formatNotFound(array $r, NotFoundHttpException $e)
    {
        $r['code'] = 404;
        $r['message'] = "Not Found.";

        return $r;
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json($this->convertExceptionToArray($exception), $exception->status);
    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function isValidationException(Exception $e)
    {
        return $e instanceof ValidationException;
    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function isNotFoundException(Exception $e)
    {
        return $e instanceof NotFoundHttpException;
    }
}
