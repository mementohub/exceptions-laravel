<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class NotFoundHttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(404);

        $data['message'] = $e->getMessage();

        return $data;
    }
}