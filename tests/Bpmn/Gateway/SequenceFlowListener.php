<?php 

namespace Tests\Bpmn\Gateway;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class SequenceFlowListener implements PhpDelegateInterface
{
    public function execute(DelegateExecutionInterface $execution)
    {
        $processEngineServices = $execution->getProcessEngineServices();
        $runtimeService = $processEngineServices->getRuntimeService();
        $act = $runtimeService->getActivityInstance($execution->getProcessInstanceId());
    }
}
