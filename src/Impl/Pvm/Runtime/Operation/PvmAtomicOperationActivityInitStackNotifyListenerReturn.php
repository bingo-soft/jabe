<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Impl\Pvm\Runtime\{
    ScopeInstantiationContext,
    InstantiationStack,
    PvmExecutionImpl
};
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationActivityInitStackNotifyListenerReturn extends PvmAtomicOperationActivityInstanceStart
{
    public function getCanonicalName(): ?string
    {
        return "activity-init-stack-notify-listener-return";
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        $activity = $execution->getActivity();

        if ($activity !== null) {
            return $activity;
        } else {
            $parent = $execution->getParent();
            if ($parent !== null) {
                return $this->getScope($execution->getParent());
            }
            return $execution->getProcessDefinition();
        }
    }

    public function getEventName(): ?string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        parent::eventNotificationsCompleted($execution);

        $startContext = $execution->getScopeInstantiationContext();
        $instantiationStack = $startContext->getInstantiationStack();

        // if the stack has been instantiated
        if (empty($instantiationStack->getActivities())) {
            // done
            $execution->disposeScopeInstantiationContext();
            return;
        } else {
            // else instantiate the activity stack further
            $execution->setActivity(null);
            $execution->performOperation(self::activityInitStackAndReturn());
        }
    }
}
