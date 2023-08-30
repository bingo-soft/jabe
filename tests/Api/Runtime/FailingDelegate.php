<?php

namespace Tests\Api\Runtime;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class FailingDelegate implements PhpDelegateInterface
{
    public const EXCEPTION_MESSAGE = "Expected_exception.";

    public function execute(DelegateExecutionInterface $execution)
    {
        $fail = $execution->getVariable("fail");
        $message = $execution->hasVariable("message") ? $execution->getVariable("message") : self::EXCEPTION_MESSAGE;

        if ($fail == null || $fail == true) {
            throw new ProcessEngineException($message);
        }
    }
}
