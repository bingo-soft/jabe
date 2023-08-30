<?php

namespace Tests\Bpmn\Event\Message;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class DummyServiceTask implements PhpDelegateInterface
{
    public static $wasExecuted = false;

    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "### Hello world from DummyService task\n");
        self::$wasExecuted = true;
    }
}
