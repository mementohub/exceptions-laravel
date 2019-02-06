<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;
use Illuminate\Support\Arr;

class ExceptionFormatter extends BaseFormatter
{
    /**
     * @param Exception $e
     * @return array
     */
    public function format(Exception $e)
    {
        $data = [
            'error' => [
                'id' => $e->id ?? null,
                'code' => $e->getCode() ?? 500,
                'message' => $e->getMessage(),
            ]
        ];

        if ($this->debug) {
            $debug = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ];
            $data['error'] = array_merge($data['error'], $debug);
        }

        $data['error'] = array_filter($data['error']);
        return $data;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param int $status_code
     * @return ExceptionFormatter
     */
    public function setStatusCode(int $status_code)
    {
        $this->status_code = $status_code;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return ExceptionFormatter
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }
}
