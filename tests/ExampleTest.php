<?php

namespace iMemento\Exceptions\Laravel\Tests;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use iMemento\Exceptions\Laravel\ExceptionHandler;
use iMemento\Exceptions\Laravel\ExceptionsServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExampleTest extends TestCase
{
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', ExceptionHandler::class);
    }

    protected function getPackageProviders($app)
    {
        return [ExceptionsServiceProvider::class];
    }

    /**
     * Tests should be added that might me able to assert that the
     * handler is actually invoked by the Laravel Framework
     */
    public function testExample()
    {
        $exception = new NotFoundHttpException('testing scenario');
        $handler = app(ExceptionHandler::class);

        $request = Request::create('/');
        $request->headers->add([
            'accept' => 'application/json'
        ]);

        $response = $handler->render($request, $exception);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        
        $this->assertEquals('Not Found.', data_get(json_decode($response->getContent()), 'error.message'));
    }
}
