<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

interface ExecutionObserverInterface
{
    /**
     * Callback which is called in the clearExecution method of the ExecutionEntity.
     *
     * @param execution the execution which is been observed
     */
    public function onClear(ExecutionEntity $execution): void;
}
