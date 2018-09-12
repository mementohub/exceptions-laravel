<?php

namespace iMemento\Exceptions\Laravel;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as LaravelHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use iMemento\Exceptions\Laravel\Formatters\BaseFormatter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandler extends LaravelHandler
{
    protected $formatters;
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

        $this->formatters = $this->config['formatters'];
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
     * @throws \ReflectionException
     */
    public function render($request, Exception $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);

        if ($request->expectsJson())
            return $this->callFormatter($e);

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException || $e instanceof AccessDeniedHttpException) {
            return redirect()->guest(route('login'));
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        return $this->prepareResponse($request, $e);
    }

    /**
     * @param Exception $e
     * @return JsonResponse
     * @throws \ReflectionException
     */
    protected function callFormatter(Exception $e)
    {
        foreach($this->formatters as $exception_type => $formatter) {
            if (! ($e instanceof $exception_type))
                continue;

            if (
                class_exists($formatter) &&
                (new ReflectionClass($formatter))->isSubclassOf(new ReflectionClass(BaseFormatter::class))
            ) {
                throw new InvalidArgumentException("$formatter is not a valid formatter class.");
            }

            $formatter_instance = new $formatter($this->config, $this->debug);
            $formatted = $formatter_instance->format($e);

            //todo move the response in the formatter? just extract code and headers maybe
            //handle different status codes

            return new JsonResponse(
                $formatted,
                $this->isHttpException($e) ? $e->getStatusCode() : 500,
                $this->isHttpException($e) ? $e->getHeaders() : [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        }
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
    //todo keep this for render purposes?
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
