<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Batch\BatchEntity;
use Jabe\Impl\Cfg\CommandCheckerInterface;
use Jabe\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\SuspensionState;

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
