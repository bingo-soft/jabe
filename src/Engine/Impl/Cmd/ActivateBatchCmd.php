<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Batch\BatchEntity;
use Jabe\Engine\Impl\Cfg\CommandCheckerInterface;
use Jabe\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;

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
