<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Variable\VariableMapInterface;

class ExecutionVariableSnapshotObserver implements ExecutionObserverInterface
{
    /**
   * The variables which are observed during the execution.
   */
    protected $variableSnapshot;

    protected $execution;

    protected bool $localVariables = true;
    protected bool $deserializeValues = false;

    public function __construct(ExecutionEntity $executionEntity, ?bool $localVariables = true, ?bool $deserializeValues = false)
    {
        $this->execution = $executionEntity;
        $this->execution->addExecutionObserver($this);
        $this->localVariables = $localVariables;
        $this->deserializeValues = $deserializeValues;
    }

    public function onClear(ExecutionEntity $execution): void
    {
        if ($this->variableSnapshot === null) {
            $this->variableSnapshot = $this->getVariables($this->localVariables);
        }
    }

    public function getVariables(?bool $localVariables = null): VariableMapInterface
    {
        if ($localVariables === null) {
            if ($this->variableSnapshot === null) {
                return $this->getVariables($this->localVariables);
            } else {
                return $this->variableSnapshot;
            }
        } else {
            return $this->localVariables ? $this->execution->getVariablesLocalTyped($this->deserializeValues) : $this->execution->getVariablesTyped($this->deserializeValues);
        }
    }
}
