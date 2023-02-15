<?php 

namespace Tests\Bpmn\Gateway;

use Jabe\Impl\Bpmn\Behavior\AbstractBpmnActivityBehavior;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class SignalableBehavior extends AbstractBpmnActivityBehavior
{
    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        fwrite(STDERR, "*** Hello world from execute method ***\n");
    }

    public function signal(/*?string*/$execution, ?string $signalName = null, $signalData = null, array $processVariables = []): void
    {
        fwrite(STDERR, "*** Hello world from signal method ***\n");
        $this->leave($execution);
    }
}
