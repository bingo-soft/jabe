<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationDeleteCascade implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return false;
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }

    public function execute(PvmExecutionImpl $execution): void
    {
        do {
            $nextLeaf = $this->findNextLeaf($execution);

            // nextLeaf can already be removed, if it was the concurrent parent of the previous leaf.
            // In that case, DELETE_CASCADE_FIRE_ACTIVITY_END on the previousLeaf already removed
            // nextLeaf, so calling DELETE_CASCADE_FIRE_ACTIVITY_END again would incorrectly
            // invoke execution listeners
            if ($nextLeaf->isDeleteRoot() && $nextLeaf->isRemoved()) {
                return;
            }

            // propagate properties
            $deleteRoot = $this->getDeleteRoot($execution);
            if ($deleteRoot != null) {
                $nextLeaf->setSkipCustomListeners($deleteRoot->isSkipCustomListeners());
                $nextLeaf->setSkipIoMappings($deleteRoot->isSkipIoMappings());
                $nextLeaf->setExternallyTerminated($deleteRoot->isExternallyTerminated());
            }

            $subProcessInstance = $nextLeaf->getSubProcessInstance();
            if ($subProcessInstance != null) {
                if ($deleteRoot->isSkipSubprocesses()) {
                    $subProcessInstance->setSuperExecution(null);
                } else {
                    $subProcessInstance->deleteCascade(
                        $execution->getDeleteReason(),
                        $nextLeaf->isSkipCustomListeners(),
                        $nextLeaf->isSkipIoMappings(),
                        $nextLeaf->isExternallyTerminated(),
                        $nextLeaf->isSkipSubprocesses()
                    );
                }
            }

            $nextLeaf->performOperation(self::deleteCascadeFireActivityEnd());
        } while (!$nextLeaf->isDeleteRoot());
    }

    protected function findNextLeaf(PvmExecutionImpl $execution): PvmExecutionImpl
    {
        if ($execution->hasChildren()) {
            return $this->findNextLeaf($execution->getExecutions()[0]);
        }
        return $execution;
    }

    protected function getDeleteRoot(PvmExecutionImpl $execution): ?PvmExecutionImpl
    {
        if ($execution == null) {
            return null;
        } elseif ($execution->isDeleteRoot()) {
            return $execution;
        } else {
            return $this->getDeleteRoot($execution->getParent());
        }
    }

    public function getCanonicalName(): string
    {
        return "delete-cascade";
    }
}
