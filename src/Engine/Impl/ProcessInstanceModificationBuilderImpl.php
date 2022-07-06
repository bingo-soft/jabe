<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\Impl\Cmd\{
    AbstractInstantiationCmd,
    AbstractProcessInstanceModificationCommand,
    ActivityAfterInstantiationCmd,
    ActivityBeforeInstantiationCmd,
    ActivityCancellationCmd,
    ActivityInstanceCancellationCmd,
    ModifyProcessInstanceAsyncCmd,
    ModifyProcessInstanceCmd,
    TransitionInstanceCancellationCmd,
    TransitionInstantiationCmd
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\{
    ProcessInstanceModificationBuilderInterface,
    ProcessInstanceModificationInstantiationBuilderInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class ProcessInstanceModificationBuilderImpl implements ProcessInstanceModificationInstantiationBuilderInterface
{
    protected $commandExecutor;
    protected $commandContext;
    protected $processInstanceId;
    protected $modificationReason;
    protected $skipCustomListeners = false;
    protected $skipIoMappings = false;
    protected $externallyTerminated = false;
    protected $annotation;

    protected $operations = [];

    // variables not associated with an activity that are to be set on the instance itself
    protected $processVariables;

    public function __construct($contextOrExecutorOrProcessInstanceId = null, string $processInstanceId = null, string $modificationReason = null)
    {
        if ($contextOrExecutorOrProcessInstanceId instanceof CommandExecutorInterface) {
            $this->commandExecutor = $contextOrExecutorOrProcessInstanceId;
            EnsureUtil::ensureNotNull(NotValidException::class, "processInstanceId", $processInstanceId);
            $this->processInstanceId = $processInstanceId;
        } elseif ($contextOrExecutorOrProcessInstanceId instanceof CommandContext) {
            $this->commandContext = $contextOrExecutorOrProcessInstanceId;
            EnsureUtil::ensureNotNull(NotValidException::class, "processInstanceId", $processInstanceId);
            $this->processInstanceId = $processInstanceId;
        } elseif (is_string($contextOrExecutorOrProcessInstanceId)) {
            $this->processInstanceId = $contextOrExecutorOrProcessInstanceId;
        }
        if ($modificationReason !== null) {
            $this->modificationReason = $modificationReason;
        }
        $this->processVariables = new VariableMapImpl();
    }

    public function cancelActivityInstance(string $activityInstanceId): ProcessInstanceModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityInstanceId", $activityInstanceId);
        $this->operations[] = new ActivityInstanceCancellationCmd($this->processInstanceId, $activityInstanceId, $this->modificationReason);
        return $this;
    }

    public function cancelTransitionInstance(string $transitionInstanceId): ProcessInstanceModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "transitionInstanceId", $transitionInstanceId);
        $this->operations[] = new TransitionInstanceCancellationCmd($this->processInstanceId, $transitionInstanceId);
        return $this;
    }

    public function cancelAllForActivity(string $activityId): ProcessInstanceModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        $this->operations[] = new ActivityCancellationCmd($this->processInstanceId, $activityId);
        return $this;
    }

    public function cancellationSourceExternal(bool $external): ProcessInstanceModificationBuilderInterface
    {
        $this->externallyTerminated = $external;
        return $this;
    }

    public function startBeforeActivity(string $activityId, ?string $ancestorActivityInstanceId = null): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        //EnsureUtil::ensureNotNull(NotValidException::class, "ancestorActivityInstanceId", $ancestorActivityInstanceId);
        $currentInstantiation = new ActivityBeforeInstantiationCmd($this->processInstanceId, $activityId, $ancestorActivityInstanceId);
        $this->operations[] = $currentInstantiation;
        return $this;
    }

    public function startAfterActivity(string $activityId, ?string $ancestorActivityInstanceId = null): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityId", $activityId);
        //EnsureUtil::ensureNotNull(NotValidException::class, "ancestorActivityInstanceId", $ancestorActivityInstanceId);
        $currentInstantiation = new ActivityAfterInstantiationCmd($this->processInstanceId, $activityId, $ancestorActivityInstanceId);
        $this->operations[] = $currentInstantiation;
        return $this;
    }

    public function startTransition(string $transitionId, ?string $ancestorActivityInstanceId = null): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "transitionId", $transitionId);
        EnsureUtil::ensureNotNull(NotValidException::class, "ancestorActivityInstanceId", $ancestorActivityInstanceId);
        $currentInstantiation = new TransitionInstantiationCmd($this->processInstanceId, $transitionId, $ancestorActivityInstanceId);
        $this->operations[] = $currentInstantiation;
        return $this;
    }

    protected function getCurrentInstantiation(): ?AbstractInstantiationCmd
    {
        if (empty($this->operations)) {
            return null;
        }

        // casting should be safe
        $lastInstantiationCmd = $this->operations[count($this->operations) - 1];

        if (!($lastInstantiationCmd instanceof AbstractInstantiationCmd)) {
            throw new ProcessEngineException("last instruction is not an instantiation");
        }

        return $lastInstantiationCmd;
    }

    public function setVariable(string $name, $value): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull("Variable name must not be null", "name", $name);

        $currentInstantiation = $this->getCurrentInstantiation();
        if ($currentInstantiation !== null) {
            $currentInstantiation->addVariable($name, $value);
        } else {
            $this->processVariables->put($name, $value);
        }

        return $this;
    }

    public function setVariableLocal(string $name, $value): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull("Variable name must not be null", "name", $name);

        $currentInstantiation = $this->getCurrentInstantiation();
        if ($currentInstantiation !== null) {
            $currentInstantiation->addVariableLocal($name, $value);
        } else {
            $this->processVariables->put($name, $value);
        }

        return $this;
    }

    public function setVariables(array $variables): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull("Variable map must not be null", "variables", $variables);

        $currentInstantiation = $this->getCurrentInstantiation();
        if ($currentInstantiation !== null) {
            $currentInstantiation->addVariables($variables);
        } else {
            $this->processVariables->putAll($variables);
        }
        return $this;
    }

    public function setVariablesLocal(array $variables): ProcessInstanceModificationInstantiationBuilderInterface
    {
        EnsureUtil::ensureNotNull("Variable map must not be null", "variablesLocal", $variables);

        $currentInstantiation = $this->getCurrentInstantiation();
        if ($currentInstantiation !== null) {
            $currentInstantiation->addVariablesLocal($variables);
        } else {
            $this->processVariables->putAll($variables);
        }
        return $this;
    }

    public function setAnnotation(string $annotation): ProcessInstanceModificationBuilderInterface
    {
        EnsureUtil::ensureNotNull("Annotation must not be null", "annotation", $annotation);
        $this->annotation = $annotation;
        return $this;
    }

    public function execute(bool $writeUserOperationLog = true, bool $skipCustomListeners = false, bool $skipIoMappings = false): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;

        $cmd = new ModifyProcessInstanceCmd($this, $writeUserOperationLog);
        if ($this->commandExecutor !== null) {
            $this->commandExecutor->execute($cmd);
        } else {
            $cmd->execute($this->commandContext);
        }
    }

    public function executeAsync(bool $skipCustomListeners = false, bool $skipIoMapping = false): BatchInterface
    {
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;

        return $this->commandExecutor->execute(new ModifyProcessInstanceAsyncCmd($this));
    }

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function getCommandContext(): CommandContext
    {
        return $this->commandContext;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getModificationOperations(): array
    {
        return $this->operations;
    }

    public function setModificationOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMappings;
    }

    public function isExternallyTerminated(): bool
    {
        return $this->externallyTerminated;
    }

    public function setSkipCustomListeners(bool $skipCustomListeners): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
    }

    public function setSkipIoMappings(bool $skipIoMappings): void
    {
        $this->skipIoMappings = $skipIoMappings;
    }

    public function getProcessVariables(): VariableMapInterface
    {
        return $this->processVariables;
    }

    public function getModificationReason(): string
    {
        return $this->modificationReason;
    }

    public function setModificationReason(string $modificationReason): void
    {
        $this->modificationReason = $modificationReason;
    }

    public function getAnnotation(): string
    {
        return $this->annotation;
    }

    public function setAnnotationInternal(string $annotation): void
    {
        $this->annotation = $annotation;
    }
}
