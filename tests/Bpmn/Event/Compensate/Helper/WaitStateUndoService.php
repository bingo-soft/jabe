<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Impl\Bpmn\Behavior\{
    AbstractBpmnActivityBehavior,
    SignallableActivityBehaviorInterface
};
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class WaitStateUndoService extends AbstractBpmnActivityBehavior implements SignallableActivityBehaviorInterface
{
    private $counterName;

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        fwrite(STDERR, "*** Hello world from WaitStateUndoService.execute method ***\n");
        $variableName = $this->counterName->getValue($execution);
        $variable = $execution->getVariable($variableName);
        if ($variable === null) {
            $execution->setVariable($variableName, 1);
        } else {
            $execution->setVariable($variableName, intval($variable) + 1);
        }
    }

    public function signal(/*?string*/$execution, ?string $signalName = null, $signalData = null, array $processVariables = []): void
    {
        fwrite(STDERR, "*** Hello world from WaitStateUndoService.signal method ***\n");
        $this->leave($execution);
    }
}
