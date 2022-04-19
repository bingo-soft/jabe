<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Batch\BatchEntity;
use BpmPlatform\Engine\Impl\Cfg\CommandCheckerInterface;
use BpmPlatform\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;

class ActivateBatchCmd extends AbstractSetBatchStateCmd
{
    public function __construct(string $batchId)
    {
        parent::__construct($batchId);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function checkAccess(CommandCheckerInterface $checker, BatchEntity $batch): void
    {
        $checker->checkActivateBatch($batch);
    }

    protected function createSetJobDefinitionStateCommand(UpdateJobDefinitionSuspensionStateBuilderImpl $builder): AbstractSetJobDefinitionStateCmd
    {
        return new ActivateJobDefinitionCmd($builder);
    }

    protected function getUserOperationType(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_BATCH;
    }
}
