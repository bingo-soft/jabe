<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Runtime\ProcessInstanceWithVariablesInterface;
use Jabe\Engine\Variable\VariableMapInterface;

class ProcessInstanceWithVariablesImpl implements ProcessInstanceWithVariablesInterface
{
    protected $executionEntity;
    protected $variables;

    public function __construct(ExecutionEntity $executionEntity, VariableMapInterface $variables)
    {
        $this->executionEntity = $executionEntity;
        $this->variables = $variables;
    }

    public function getExecutionEntity(): ExecutionEntity
    {
        return $this->executionEntity;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->executionEntity->getProcessDefinitionId();
    }

    public function getBusinessKey(): ?string
    {
        return $this->executionEntity->getBusinessKey();
    }

    /*public String getCaseInstanceId() {
        return executionEntity.getCaseInstanceId();
    }*/

    public function isSuspended(): bool
    {
        return $this->executionEntity->isSuspended();
    }

    public function getId(): ?string
    {
        return $this->executionEntity->getId();
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->executionEntity->getRootProcessInstanceId();
    }

    public function isEnded(): bool
    {
        return $this->executionEntity->isEnded();
    }

    public function getProcessInstanceId(): string
    {
        return $this->executionEntity->getProcessInstanceId();
    }

    public function getTenantId(): ?string
    {
        return $this->executionEntity->getTenantId();
    }
}
