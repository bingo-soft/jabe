<?php

namespace BpmPlatform\Engine\Impl\Core\Operation;

use BpmPlatform\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    DelegateListenerInterface
};
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;
use BpmPlatform\Engine\Impl\Core\Model\CoreModelElement;
use BpmPlatform\Engine\Impl\Pvm\PvmException;

abstract class AbstractEventAtomicOperation implements CoreAtomicOperationInterface
{
    public function isAsync(CoreExecution $execution): bool
    {
        return false;
    }

    public function execute(CoreExecution $execution): void
    {
        $scope = $this->getScope($execution);
        $listeners = $this->getListeners($scope, $execution);
        $listenerIndex = $execution->getListenerIndex();

        if ($listenerIndex == 0) {
            $execution = $this->eventNotificationsStarted($execution);
        }

        if (!$this->isSkipNotifyListeners($execution)) {
            if (count($listeners) > $listenerIndex) {
                $execution->setEventName(getEventName());
                $execution->setEventSource($scope);
                $listener = $listeners[$listenerIndex];
                $execution->setListenerIndex($listenerIndex + 1);

                try {
                    $execution->invokeListener($listener);
                } catch (\Exception $ex) {
                    $this->eventNotificationsFailed($execution, $ex);
                    // do not continue listener invocation once a listener has failed
                    return;
                }
                $execution->performOperationSync($this);
            } else {
                $this->resetListeners($execution);
                $this->eventNotificationsCompleted($execution);
            }
        } else {
            $this->eventNotificationsCompleted($execution);
        }
    }

    protected function resetListeners(CoreExecution $execution): void
    {
        $execution->setListenerIndex(0);
        $execution->setEventName(null);
        $execution->setEventSource(null);
    }

    protected function getListeners(CoreModelElement $scope, CoreExecution $execution): array
    {
        if ($execution->isSkipCustomListeners()) {
            return $scope->getBuiltInListeners($this->getEventName());
        } else {
            return $scope->getListeners($this->getEventName());
        }
    }

    protected function isSkipNotifyListeners(CoreExecution $execution): bool
    {
        return false;
    }

    protected function eventNotificationsStarted(CoreExecution $execution): CoreExecution
    {
        // do nothing
        return $execution;
    }

    abstract protected function getScope(CoreExecution $execution): CoreModelElement;
    abstract protected function getEventName(): string;
    abstract protected function eventNotificationsCompleted(CoreExecution $execution): void;

    protected function eventNotificationsFailed(CoreExecution $execution, \Exception $exception): void
    {
        throw new PvmException("couldn't execute event listener : " . $exception->getMessage());
    }
}
