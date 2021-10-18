<?php

namespace BpmPlatform\Engine;

class ProcessEngineException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
