<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class AuthenticationExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(401);

        return [
            'error' => [
                'code' => 401,
                'message' => $e->getMessage(),
            ]
        ];
    }
}
