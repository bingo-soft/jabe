<?php

namespace Jabe\Impl;

use Jabe\Batch\BatchInterface;
use Jabe\Exception\NotValidException;
use Jabe\Impl\Cmd\{
    AbstractProcessInstanceModificationCommand,
    ActivityAfterInstantiationCmd,
    ActivityBeforeInstantiationCmd,
    ActivityCancellationCmd,
    ProcessInstanceModificationBatchCmd,
    ProcessInstanceModificationCmd,
    TransitionInstantiationCmd
};
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\{
    ModificationBuilderInterface,
    ProcessInstanceQueryInterface
};

class ModificationBuilderImpl implements ModificationBuilderInterface
{
    protected $commandExecutor;
    protected $processInstanceQuery;
    protected $processInstanceIds;
    protected $instructions;
    protected $processDefinitionId;
    protected $skipCustomListeners;
    protected $skipIoMappings;
    protected $annotation;

    public function __construct(CommandExecutorInterface $commandExecutor, ?string $processDefinitionId)
    {
        $this->commandExecutor = $commandExecutor;
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        $this->processInstanceIds = [];
        $this->instructions = [];
    }

    public function startBeforeActivity(?string $activityId): ModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $this->instructions[] = new ActivityBeforeInstantiationCmd(null, $activityId);
        return $this;
    }

    public function startAfterActivity(?string $activityId): ModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $this->instructions[] = new ActivityAfterInstantiationCmd($activityId);
        return $this;
    }

    public function startTransition(?string $transitionId): ModificationBuilder
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "transitionId", $transitionId);
        $this->instructions[] = new TransitionInstantiationCmd($transitionId);
        return $this;
    }

    public function cancelAllForActivity(?string $activityId, bool $cancelCurrentActiveActivityInstances = false): ModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $activityCancellationCmd = new ActivityCancellationCmd($activityId);
        $activityCancellationCmd->setCancelCurrentActiveActivityInstances($cancelCurrentActiveActivityInstances);
        $this->instructions[] = $activityCancellationCmd;
        return $this;
    }

    public function processInstanceIds(array $processInstanceIds): ModificationBuilderInterface
    {
        if (empty($this->processInstanceIds)) {
            $this->processInstanceIds = [];
        } else {
            $this->processInstanceIds = $processInstanceIds;
        }
        return $this;
    }

    public function processInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): ModificationBuilderInterface
    {
        $this->processInstanceQuery = $processInstanceQuery;
        return $this;
    }

    public function skipCustomListeners(): ModificationBuilderInterface
    {
        $this->skipCustomListeners = true;
        return $this;
    }

    public function skipIoMappings(): ModificationBuilderInterface
    {
        $this->skipIoMappings = true;
        return $this;
    }

    public function setAnnotation(?string $annotation): ModificationBuilderInterface
    {
        $this->annotation = $annotation;
        return $this;
    }

    public function execute(bool $writeUserOperationLog = true)
    {
        $this->commandExecutor->execute(new ProcessInstanceModificationCmd($this, $writeUserOperationLog));
    }

    public function executeAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new ProcessInstanceModificationBatchCmd($this));
    }

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function getProcessInstanceQuery(): ProcessInstanceQueryInterface
    {
        return $this->processInstanceQuery;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(?string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function setInstructions(array $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMappings;
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function setAnnotationInternal(?string $annotation): void
    {
        $this->annotation = $annotation;
    }
}
