<?php

namespace Jabe\Impl;

use Jabe\Batch\BatchInterface;
use Jabe\Exception\NotValidException;
use Jabe\History\HistoricProcessInstanceQueryInterface;
use Jabe\Impl\Batch\RestartProcessInstancesBatchCmd;
use Jabe\Impl\Cmd\{
    AbstractProcessInstanceModificationCommand,
    ActivityAfterInstantiationCmd,
    ActivityBeforeInstantiationCmd,
    RestartProcessInstancesCmd,
    TransitionInstantiationCmd
};
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\RestartProcessInstanceBuilderInterface;

class RestartProcessInstanceBuilderImpl implements RestartProcessInstanceBuilderInterface
{
    protected $commandExecutor;
    protected $processInstanceIds = [];
    protected $instructions = [];
    protected $processDefinitionId;
    protected $query;
    protected bool $initialVariables = false;
    protected bool $skipCustomListeners = false;
    protected bool $skipIoMappings = false;
    protected bool $withoutBusinessKey = false;

    public function __construct($processDefinitionIdOrExecutor, ?string $processDefinitionId = null)
    {
        if ($processDefinitionIdOrExecutor instanceof CommandExecutorInterface) {
            EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionId", $processDefinitionId);
            $this->commandExecutor = $processDefinitionIdOrExecutor;
            $this->processDefinitionId = $processDefinitionId;
        } elseif (is_string($processDefinitionIdOrExecutor) && $processDefinitionId === null) {
            $this->processDefinitionId = $processDefinitionIdOrExecutor;
        }
    }

    public function startBeforeActivity(?string $activityId): RestartProcessInstanceBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $this->instructions[] = new ActivityBeforeInstantiationCmd(null, $activityId);
        return $this;
    }

    public function startAfterActivity(?string $activityId): RestartProcessInstanceBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $this->instructions[] = new ActivityAfterInstantiationCmd(null, $activityId);
        return $this;
    }

    public function startTransition(?string $transitionId): RestartProcessInstanceBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $transitionId);
        $this->instructions[] = new TransitionInstantiationCmd(null, $transitionId);
        return $this;
    }

    public function execute()
    {
        $this->commandExecutor->execute(new RestartProcessInstancesCmd($this->commandExecutor, $this));
    }

    public function executeAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new RestartProcessInstancesBatchCmd($this->commandExecutor, $this));
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function historicProcessInstanceQuery(HistoricProcessInstanceQueryInterface $query): RestartProcessInstanceBuilderInterface
    {
        $this->query = $query;
        return $this;
    }

    public function getHistoricProcessInstanceQuery(): HistoricProcessInstanceQueryInterface
    {
        return $this->query;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setInstructions(array $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function setProcessDefinitionId(?string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function processInstanceIds(array $processInstanceIds): RestartProcessInstanceBuilderInterface
    {
        $this->processInstanceIds = array_merge($this->processInstanceIds, $processInstanceIds);
        return $this;
    }

    public function initialSetOfVariables(): RestartProcessInstanceBuilderInterface
    {
        $this->initialVariables = true;
        return $this;
    }

    public function isInitialVariables(): bool
    {
        return $this->initialVariables;
    }

    public function skipCustomListeners(): RestartProcessInstanceBuilderInterface
    {
        $this->skipCustomListeners = true;
        return $this;
    }

    public function skipIoMappings(): RestartProcessInstanceBuilderInterface
    {
        $this->skipIoMappings = true;
        return $this;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMappings;
    }

    public function withoutBusinessKey(): RestartProcessInstanceBuilderInterface
    {
        $this->withoutBusinessKey = true;
        return $this;
    }

    public function isWithoutBusinessKey(): bool
    {
        return $this->withoutBusinessKey;
    }
}
