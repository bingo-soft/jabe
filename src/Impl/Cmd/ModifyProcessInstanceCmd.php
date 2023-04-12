<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\{
    ProcessEngineLogger,
    ProcessInstanceModificationBuilderImpl
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};
use Jabe\Impl\Util\ModificationUtil;

class ModifyProcessInstanceCmd implements CommandInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;
    protected $builder;
    protected $writeOperationLog;

    public function __construct(ProcessInstanceModificationBuilderImpl $processInstanceModificationBuilder, bool $writeOperationLog = true)
    {
        $this->builder = $processInstanceModificationBuilder;
        $this->writeOperationLog = $writeOperationLog;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processInstanceId = $this->builder->getProcessInstanceId();

        $executionManager = $commandContext->getExecutionManager();
        $processInstance = $executionManager->findExecutionById($processInstanceId);

        $this->ensureProcessInstanceExist($processInstanceId, $processInstance);

        $this->checkUpdateProcessInstance($processInstance, $commandContext);

        $processInstance->setPreserveScope(true);

        $instructions = $this->builder->getModificationOperations();

        $this->checkCancellation($commandContext);
        for ($i = 0; $i < count($instructions); $i += 1) {
            $instruction = $instructions[$i];
            //LOG.debugModificationInstruction(processInstanceId, i + 1, instruction.describe());
            $instruction->setSkipCustomListeners($this->builder->isSkipCustomListeners());
            $instruction->setSkipIoMappings($this->builder->isSkipIoMappings());
            $instruction->setExternallyTerminated($this->builder->isExternallyTerminated());
            $instruction->execute($commandContext);
        }

        $processInstance = $executionManager->findExecutionById($processInstanceId);

        if (!$processInstance->hasChildren() && !$processInstance->isCanceled() && !$processInstance->isRemoved()) {
            if ($processInstance->getActivity() === null) {
                // process instance was cancelled
                $this->checkDeleteProcessInstance($processInstance, $commandContext);
                $this->deletePropagate(
                    $processInstance,
                    $this->builder->getModificationReason(),
                    $this->builder->isSkipCustomListeners(),
                    $this->builder->isSkipIoMappings(),
                    $this->builder->isExternallyTerminated()
                );
            } elseif ($processInstance->isEnded()) {
                // process instance has ended regularly
                $processInstance->propagateEnd();
            }
        }

        if ($this->writeOperationLog) {
            $commandContext->getOperationLogManager()->logProcessInstanceOperation(
                $this->getLogEntryOperation(),
                $processInstanceId,
                null,
                null,
                [PropertyChange::emptyChange()],
                $this->builder->getAnnotation()
            );
        }

        return null;
    }

    private function checkCancellation(CommandContext $commandContext): void
    {
        foreach ($this->builder->getModificationOperations() as $instruction) {
            if (
                $instruction instanceof ActivityCancellationCmd
                && $instruction->cancelCurrentActiveActivityInstances
            ) {
                $activityInstanceTree = $commandContext->runWithoutAuthorization(function () use ($commandContext, $instruction) {
                    $cmd = new GetActivityInstanceCmd($instruction->processInstanceId);
                    return $cmd->execute($commandContext);
                });
                $instruction->setActivityInstanceTreeToCancel($activityInstanceTree);
            }
        }
    }

    protected function ensureProcessInstanceExist(?string $processInstanceId, ?ExecutionEntity $processInstance): void
    {
        if ($processInstance === null) {
            //throw LOG.processInstanceDoesNotExist(processInstanceId);
        }
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_MODIFY_PROCESS_INSTANCE;
    }

    protected function checkUpdateProcessInstance(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstance($execution);
        }
    }

    protected function checkDeleteProcessInstance(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteProcessInstance($execution);
        }
    }

    protected function deletePropagate(ExecutionEntity $processInstance, ?string $deleteReason, bool $skipCustomListeners, bool $skipIoMappings, bool $externallyTerminated): void
    {
        $topmostDeletableExecution = $processInstance;
        $parentScopeExecution = $topmostDeletableExecution->getParentScopeExecution(true);

        while ($parentScopeExecution !== null && count($parentScopeExecution->getNonEventScopeExecutions()) <= 1) {
            $topmostDeletableExecution = $parentScopeExecution;
            $parentScopeExecution = $topmostDeletableExecution->getParentScopeExecution(true);
        }

        $topmostDeletableExecution->deleteCascade($deleteReason, $skipCustomListeners, $skipIoMappings, $externallyTerminated, false);
        ModificationUtil::handleChildRemovalInScope($topmostDeletableExecution);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
