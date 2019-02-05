<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class NotFoundHttpExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode(404);

        return [
            'error' => [
                'code' => 404,
                'message' => 'Not Found.',
            ]
        ];
    }
}
