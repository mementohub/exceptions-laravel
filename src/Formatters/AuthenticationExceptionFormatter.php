<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class AuthenticationExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(401);

        $data['message'] = $e->getMessage();
        $data['code'] = 401;

        return $data;
    }
}
