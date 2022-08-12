<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\ProcessEngineException;
use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;
use Jabe\Impl\Pvm\Process\ActivityStartBehavior;

abstract class AbstractPvmAtomicOperationTransitionNotifyListenerTake extends AbstractPvmEventAtomicOperation
{
    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        $destination = $execution->getTransition()->getDestination();

        // check start behavior of next activity
        switch ($destination->getActivityStartBehavior()) {
            case ActivityStartBehavior::DEFAULT:
                $execution->setActivity($destination);
                $execution->dispatchDelayedEventsAndPerformOperation(self::transitionCreateScope());
                break;
            case ActivityStartBehavior::INTERRUPT_FLOW_SCOPE:
                $execution->setActivity(null);
                $execution->performOperation(self::transitionInterruptFlowScope());
                break;
            default:
                throw new ProcessEngineException(
                    "Unsupported start behavior for activity '" . $destination .
                    "' started from a sequence flow: " . $destination->getActivityStartBehavior()
                );
        }
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getTransition();
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_TAKE;
    }
}
