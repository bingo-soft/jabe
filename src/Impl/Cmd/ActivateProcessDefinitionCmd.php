<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\JobExecutor\TimerActivateProcessDefinitionHandler;
use Jabe\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;

class ActivateProcessDefinitionCmd extends AbstractSetProcessDefinitionStateCmd
{
    public function __construct(UpdateProcessDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function getDelayedExecutionJobHandlerType(): ?string
    {
        return TimerActivateProcessDefinitionHandler::TYPE;
    }

    protected function getSetJobDefinitionStateCmd(UpdateJobDefinitionSuspensionStateBuilderImpl $jobDefinitionSuspensionStateBuilder): AbstractSetJobDefinitionStateCmd
    {
        return new ActivateJobDefinitionCmd($jobDefinitionSuspensionStateBuilder);
    }

    protected function getNextCommand($processInstanceCommandBuilder = null)
    {
        return new ActivateProcessInstanceCmd($processInstanceCommandBuilder);
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_PROCESS_DEFINITION;
    }
}
