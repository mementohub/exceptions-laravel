<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class AccessDeniedHttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(403);

        return [
            'error' => [
                'code' => 403,
                'message' => $e->getMessage(),
            ]
        ];
    }
}
