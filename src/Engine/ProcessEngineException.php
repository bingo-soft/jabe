<?php

namespace Jabe\Engine;

class ProcessEngineException extends \Exception
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getCause(): ?\Throwable
    {
        return $this->getPrevious();
    }
}
