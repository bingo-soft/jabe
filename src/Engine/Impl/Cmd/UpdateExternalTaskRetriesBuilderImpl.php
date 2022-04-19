<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Batch\BatchInterface;
use BpmPlatform\Engine\ExternalTask\{
    ExternalTaskQueryInterface,
    UpdateExternalTaskRetriesBuilderInterface
};
use BpmPlatform\Engine\History\HistoricProcessInstanceQueryInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandExecutorInterface;
use BpmPlatform\Engine\Runtime\ProcessInstanceQueryInterface;

class UpdateExternalTaskRetriesBuilderImpl implements UpdateExternalTaskRetriesBuilderInterface
{
    protected $commandExecutor;

    protected $externalTaskIds = [];
    protected $processInstanceIds = [];

    protected $externalTaskQuery;
    protected $processInstanceQuery;
    protected $historicProcessInstanceQuery;

    protected $retries;

    public function __construct($el, ?int $retries = null)
    {
        if ($el instanceof CommandExecutorInterface) {
            $this->commandExecutor = $el;
        } else {
            $this->externalTaskIds = $el;
            $this->retries = $retries;
        }
    }

    public function externalTaskIds(?array $externalTaskIds = []): UpdateExternalTaskRetriesBuilderInterface
    {
        $this->externalTaskIds = $externalTaskIds;
        return $this;
    }

    public function processInstanceIds(array $processInstanceIds = []): UpdateExternalTaskRetriesBuilderInterface
    {
        $this->processInstanceIds = $processInstanceIds;
        return this;
    }

    public function externalTaskQuery(ExternalTaskQueryInterface $externalTaskQuery): UpdateExternalTaskRetriesBuilderInterface
    {
        $this->externalTaskQuery = $externalTaskQuery;
        return $this;
    }

    public function processInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): UpdateExternalTaskRetriesBuilderInterface
    {
        $this->processInstanceQuery = $processInstanceQuery;
        return $this;
    }

    public function historicProcessInstanceQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): UpdateExternalTaskRetriesBuilderInterface
    {
        $this->historicProcessInstanceQuery = $historicProcessInstanceQuery;
        return this;
    }

    public function set(int $retries): void
    {
        $this->retries = $retries;
        $this->commandExecutor->execute(new SetExternalTasksRetriesCmd($this));
    }

    public function setAsync(int $retries): BatchInterface
    {
        $this->retries = $retries;
        return $this->commandExecutor->execute(new SetExternalTasksRetriesBatchCmd($this));
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getExternalTaskIds(): array
    {
        return $this->externalTaskIds;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getExternalTaskQuery(): ExternalTaskQueryInterface
    {
        return $this->externalTaskQuery;
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
