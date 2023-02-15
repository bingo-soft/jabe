<?php 

namespace Tests\Bpmn\SendTask;

use Jabe\Impl\Bpmn\Behavior\TaskActivityBehavior;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class DummyActivityBehavior extends TaskActivityBehavior
{
    public static bool $wasExecuted = false;

    public static ?string $currentActivityId = null;
    public static ?string $currentActivityName = null;

    public function signal(/*?string*/$execution, ?string $signalName = null, $signalData = null, array $processVariables = []): void
    {
        fwrite(STDERR, "*** Hello world from signal method ***\n");
        self::$currentActivityName = $execution->getCurrentActivityName();
        self::$currentActivityId = $execution->getCurrentActivityId();
        $this->leave($execution);
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        fwrite(STDERR, "*** Hello world from performExecution method ***\n");
        self::$wasExecuted = true;
    }
}
