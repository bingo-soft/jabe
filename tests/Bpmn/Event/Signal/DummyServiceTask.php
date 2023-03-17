<?php

namespace Tests\Bpmn\Event\Signal;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class DummyServiceTask implements PhpDelegateInterface
{
    public static $wasExecuted = false;

    public function execute(DelegateExecutionInterface $execution)
    {
        self::$wasExecuted = true;
    }
}
