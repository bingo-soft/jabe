<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Impl\Cmd\{
    UpdateProcessInstancesSuspendStateBatchCmd,
    UpdateProcessInstancesSuspendStateCmd
};
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Runtime\{
    ProcessInstanceQueryInterface,
    UpdateProcessInstancesSuspensionStateBuilderInterface
};

class UpdateProcessInstancesSuspensionStateBuilderImpl implements UpdateProcessInstancesSuspensionStateBuilder
{
    protected $processInstanceIds = [];
    protected $processInstanceQuery;
    protected $historicProcessInstanceQuery;
    protected $commandExecutor;
    protected $processDefinitionId;

    public function __construct($executirOrInstances)
    {
        if (is_array($executirOrInstances)) {
            $this->processInstanceIds = $processInstanceIds;
        } elseif ($executirOrInstances instanceof CommandExecutorInterface) {
            $this->commandExecutor = $executirOrInstances;
        }
    }

    public function byProcessInstanceIds(array $processInstanceIds): UpdateProcessInstancesSuspensionStateBuilderInterface
    {
        $this->processInstanceIds = array_merge($this->processInstanceIds, $processInstanceIds);
        return $this;
    }

    public function byProcessInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): UpdateProcessInstancesSuspensionStateBuilderInterface
    {
        $this->processInstanceQuery = $processInstanceQuery;
        return $this;
    }

    public function byHistoricProcessInstanceQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): UpdateProcessInstancesSuspensionStateBuilderInterface
    {
        $this->historicProcessInstanceQuery = $historicProcessInstanceQuery;
        return $this;
    }

    public function suspend(): void
    {
        $this->commandExecutor->execute(new UpdateProcessInstancesSuspendStateCmd($this->commandExecutor, $this, true));
    }

    public function activate(): void
    {
        $this->commandExecutor->execute(new UpdateProcessInstancesSuspendStateCmd($this->commandExecutor, $this, false));
    }

    public function suspendAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new UpdateProcessInstancesSuspendStateBatchCmd($this->commandExecutor, $this, true));
    }

    public function activateAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new UpdateProcessInstancesSuspendStateBatchCmd($this->commandExecutor, $this, false));
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getProcessInstanceQuery(): ProcessInstanceQueryInterface
    {
        return $this->processInstanceQuery;
    }

    public function getHistoricProcessInstanceQuery(): HistoricProcessInstanceQueryInterface
    {
        return $this->historicProcessInstanceQuery;
    }
}
