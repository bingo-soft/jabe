<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\JobExecutor\TimerSuspendProcessDefinitionHandler;
use BpmPlatform\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;
use BpmPlatform\Engine\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

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
