<?php

namespace Jabe\Impl;

use Jabe\Batch\BatchInterface;
use Jabe\History\HistoricProcessInstanceQueryInterface;
use Jabe\Impl\Cmd\{
    UpdateProcessInstancesSuspendStateBatchCmd,
    UpdateProcessInstancesSuspendStateCmd
};
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Runtime\{
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
            $this->processInstanceIds = $executirOrInstances;
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
