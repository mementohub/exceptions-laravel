<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;

class HttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        parent::format($response, $e);
        
        if (count($headers = $e->getHeaders())) {
            $response->headers->add($headers);
        }

        $response->setStatusCode($e->getStatusCode());
    }
}
