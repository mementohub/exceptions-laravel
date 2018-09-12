<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;
use Illuminate\Http\JsonResponse;

class MessageFormatter extends BaseFormatter
{
    public function format(Exception $e)
    {
        $response->setStatusCode(500);
        $data = $response->getData(true);

        if ($this->debug) {
            $data = array_merge($data, [
                'code'   => $e->getCode(),
                'message'   => $e->getMessage(),
                'exception' => (string) $e,
                'line'   => $e->getLine(),
                'file'   => $e->getFile()
            ]);
        } else {
            $data['message'] = $this->config['server_error_production'];
        }

        $response->setData($data);
    }
}
