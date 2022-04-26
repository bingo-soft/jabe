<?php

namespace Jabe\Engine\Impl\Runtime;

use Jabe\Engine\Impl\ConditionEvaluationBuilderImpl;
use Jabe\Engine\Variable\VariableMapInterface;

class ConditionSet
{
    protected $businessKey;
    protected $processDefinitionId;
    protected $variables;
    protected $tenantId;
    protected $isTenantIdSet;

    public function __construct(ConditionEvaluationBuilderImpl $builder)
    {
        $this->businessKey = $builder->getBusinessKey();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->variables = $builder->getVariables();
        $this->tenantId = $builder->getTenantId();
        $this->isTenantIdSet = $builder->isTenantIdSet();
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function __toString()
    {
        return "ConditionSet [businessKey=" . $this->businessKey . ", processDefinitionId=" . $this->processDefinitionId
          . ", variables=" . $this->variables . ", tenantId=" . $this->tenantId . ", isTenantIdSet=" . $this->isTenantIdSet . "]";
    }
}
