<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Util\ModificationUtil;

abstract class AbstractInstanceCancellationCmd extends AbstractProcessInstanceModificationCommand
{
    protected $cancellationReason;

    public function __construct(string $processInstanceId, string $cancellationReason)
    {
        parent::__construct($processInstanceId);
        $this->cancellationReason = $cancellationReason ?? "Cancellation due to process instance modifcation";
    }

    public function execute(CommandContext $commandContext)
    {
        $sourceInstanceExecution = $this->determineSourceInstanceExecution($commandContext);

        // Outline:
        // 1. find topmost scope execution beginning at scopeExecution that has exactly
        //    one child (this is the topmost scope we can cancel)
        // 2. cancel all children of the topmost execution
        // 3. cancel the activity of the topmost execution itself (if applicable)
        // 4. remove topmost execution (and concurrent parent) if topmostExecution is not the process instance

        $topmostCancellableExecution = $sourceInstanceExecution;
        $parentScopeExecution = $topmostCancellableExecution->getParentScopeExecution(false);

        // if topmostCancellableExecution's scope execution has no other non-event-scope children,
        // we have reached the correct execution
        while ($parentScopeExecution != null && (count($parentScopeExecution->getNonEventScopeExecutions()) <= 1)) {
            $topmostCancellableExecution = $parentScopeExecution;
            $parentScopeExecution = $topmostCancellableExecution->getParentScopeExecution(false);
        }

        if ($topmostCancellableExecution->isPreserveScope()) {
            $topmostCancellableExecution->interrupt($this->cancellationReason, $this->skipCustomListeners, $this->skipIoMappings, $this->externallyTerminated);
            $topmostCancellableExecution->leaveActivityInstance();
            $topmostCancellableExecution->setActivity(null);
        } else {
            $topmostCancellableExecution->deleteCascade($this->cancellationReason, $this->skipCustomListeners, $this->skipIoMappings, $this->externallyTerminated, false);
            ModificationUtil::handleChildRemovalInScope($topmostCancellableExecution);
        }

        return null;
    }

    abstract protected function determineSourceInstanceExecution(CommandContext $commandContext): ExecutionEntity;

    protected function findSuperExecution(?ExecutionEntity $parentScopeExecution, ExecutionEntity $topmostCancellableExecution): ?ExecutionEntity
    {
        $superExecution = null;
        if ($parentScopeExecution == null) {
            $superExecution = $topmostCancellableExecution->getSuperExecution();
        }
        return $superExecution;
    }
}
