<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

class ValidationExceptionFormatter extends ExceptionFormatter
{
    public function format(Exception $e)
    {
        $this->setStatusCode($e->status);

        $data = parent::format($e);
        $data['error']['code'] = $e->status;

        $data['error']['messages'] = [];
        foreach ($e->errors() as $k => $v) {
            foreach ($v as $m) {
                $data['error']['messages'][] = [
                    'input' => $k,
                    'message' => $m,
                ];
            }
        }

        return $data;
    }
}
