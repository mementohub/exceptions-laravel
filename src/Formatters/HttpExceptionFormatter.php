<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class HttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        if (count($headers = $e->getHeaders()))
            $this->setHeaders($headers);

        return parent::format($e);
    }
}
