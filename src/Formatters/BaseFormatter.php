<?php

namespace iMemento\Exceptions\Laravel\Formatters;

use Exception;

abstract class BaseFormatter
{
    protected $config;
    protected $debug;

    protected $status_code = 500;
    protected $headers = [];

    public function __construct(array $config, $debug)
    {
        $this->config = $config;
        $this->debug = $debug;
    }

    abstract protected function format(Exception $e);

    abstract protected function getStatusCode();
    abstract protected function setStatusCode(int $status_code);

    abstract protected function getHeaders();
    abstract protected function setHeaders(array $headers);
}
