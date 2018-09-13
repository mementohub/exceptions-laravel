<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class AccessDeniedHttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(403);

        $data['message'] = $e->getMessage();
        $data['code'] = 403;

        return $data;
    }
}
