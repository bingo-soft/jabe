<?php

namespace Tests\Bpmn\Event\Timer;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class TestService implements PhpDelegateInterface
{
    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "*** Service (0) executed at " . (new \DateTime())->format('c') . "\n");
    }
}
