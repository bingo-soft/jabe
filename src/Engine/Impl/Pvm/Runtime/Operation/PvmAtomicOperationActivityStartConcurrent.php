<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationActivityStartConcurrent extends PvmAtomicOperationCreateConcurrentExecution
{

    protected function concurrentExecutionCreated(PvmExecutionImpl $propagatingExecution): void
    {
        $propagatingExecution->setActivityInstanceId(null);
        $propagatingExecution->performOperation(self::activityStartCreateScope());
    }

    public function getCanonicalName(): string
    {
        return "activity-start-concurrent";
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }
}
