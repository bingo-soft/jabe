<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\JobExecutor\TimerSuspendProcessDefinitionHandler;
use Jabe\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;
use Jabe\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

class SuspendProcessDefinitionCmd extends AbstractSetProcessDefinitionStateCmd
{
    public function __construct(UpdateProcessDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::suspended();
    }

    protected function getDelayedExecutionJobHandlerType(): string
    {
        return TimerSuspendProcessDefinitionHandler::TYPE;
    }

    protected function getSetJobDefinitionStateCmd(UpdateJobDefinitionSuspensionStateBuilderImpl $jobDefinitionSuspensionStateBuilder): AbstractSetJobDefinitionStateCmd
    {
        return new SuspendJobDefinitionCmd($jobDefinitionSuspensionStateBuilder);
    }

    protected function getNextCommand(UpdateProcessInstanceSuspensionStateBuilderImpl $processInstanceCommandBuilder): SuspendProcessInstanceCmd
    {
        return new SuspendProcessInstanceCmd($processInstanceCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND_PROCESS_DEFINITION;
    }
}
