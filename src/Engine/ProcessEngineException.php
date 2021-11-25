<?php

namespace BpmPlatform\Engine;

class ProcessEngineException extends \Exception
{
    private $cause;

    public function __construct(string $message, $code = 0, \Exception $cause = null)
    {
        parent::__construct($message, $code);

        if ($cause != null) {
            $this->cause = $cause;
        }
    }

    public function getCause(): ?\Exception
    {
        return $this->cause;
    }
}
