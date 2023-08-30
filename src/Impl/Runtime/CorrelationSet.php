<?php

namespace Jabe\Impl\Runtime;

use Jabe\Impl\MessageCorrelationBuilderImpl;
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Impl\VariableMapImpl;

class CorrelationSet
{
    protected $businessKey;
    protected $correlationKeys = [];
    protected $localCorrelationKeys = [];
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $tenantId;
    protected bool $isTenantIdSet = false;
    protected bool $isExecutionsOnly = false;

    public function __construct(MessageCorrelationBuilderImpl $builder)
    {
        $this->businessKey = $builder->getBusinessKey();
        $this->processInstanceId = $builder->getProcessInstanceId();
        $this->correlationKeys = $builder->getCorrelationProcessInstanceVariables() ?? new VariableMapImpl();
        $this->localCorrelationKeys = $builder->getCorrelationLocalVariables() ?? new VariableMapImpl();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->tenantId = $builder->getTenantId();
        $this->isTenantIdSet = $builder->isTenantIdSet();
        $this->isExecutionsOnly = $builder->isExecutionsOnly();
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getCorrelationKeys(): VariableMapInterface
    {
        return $this->correlationKeys;
    }

    public function getLocalCorrelationKeys(): VariableMapInterface
    {
        return $this->localCorrelationKeys;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isExecutionsOnly(): bool
    {
        return $this->isExecutionsOnly;
    }

    public function __toString()
    {
        return "CorrelationSet [businessKey=" . $this->businessKey . ", processInstanceId=" . $this->processInstanceId . ", processDefinitionId=" . $this->processDefinitionId . ", correlationKeys=" . json_encode($this->correlationKeys) . ", localCorrelationKeys=" . json_encode($this->localCorrelationKeys) . ", tenantId=" . $this->tenantId .
          ", isTenantIdSet=" . $this->isTenantIdSet . ", isExecutionsOnly=" . $this->isExecutionsOnly . "]";
    }
}
