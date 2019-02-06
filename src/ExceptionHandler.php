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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use iMemento\Exceptions\Laravel\Formatters\BaseFormatter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

        $this->config = config('exceptions');
        $this->debug = config('app.debug');

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
        if ($this->shouldntReport($e)) {
            return;
        } else {
            //add an unique id only if we report the error
            $e->id = Str::random(32);
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
            array_merge($this->context(), ['exception' => $e])
        );
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
                ! class_exists($formatter) ||
                ! (new ReflectionClass($formatter))->isSubclassOf(new ReflectionClass(BaseFormatter::class))
            ) {
                throw new InvalidArgumentException("$formatter is not a valid formatter class.");
            }

            $formatter_instance = new $formatter($this->config, $this->debug);
            $formatted = $formatter_instance->format($e);

            return new JsonResponse(
                $formatted,
                $formatter_instance->getStatusCode(),
                $formatter_instance->getHeaders(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        }
    }

}
