<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class ValidationExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode($e->status);

        $data = parent::format($e);
        $data['code'] = $e->status;

        $data['errors'] = [];
        foreach ($e->errors() as $k => $v) {
            foreach ($v as $m) {
                array_push($data['errors'], [
                    'input' => $k,
                    'message' => $m,
                ]);
            }
        }

        return $data;
    }
}
