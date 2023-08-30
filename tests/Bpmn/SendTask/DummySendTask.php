<?php 

namespace Tests\Bpmn\SendTask;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class DummySendTask implements PhpDelegateInterface
{
    public static bool $wasExecuted = false;

    public function execute(DelegateExecutionInterface $execution)
    {
        self::$wasExecuted = true;
        fwrite(STDERR, "*** Hello world from send task ***\n");
    }
}
