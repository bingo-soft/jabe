<?php

namespace Jabe\Engine\Impl\Runtime;

use Jabe\Engine\Impl\MessageCorrelationBuilderImpl;

class CorrelationSet
{
    protected $businessKey;
    protected $correlationKeys = [];
    protected $localCorrelationKeys = [];
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $tenantId;
    protected $isTenantIdSet;
    protected $isExecutionsOnly;

    public function __construct(MessageCorrelationBuilderImpl $builder)
    {
        $this->businessKey = $builder->getBusinessKey();
        $this->processInstanceId = $builder->getProcessInstanceId();
        $this->correlationKeys = $builder->getCorrelationProcessInstanceVariables();
        $this->localCorrelationKeys = $builder->getCorrelationLocalVariables();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->tenantId = $builder->getTenantId();
        $this->isTenantIdSet = $builder->isTenantIdSet();
        $this->isExecutionsOnly = $builder->isExecutionsOnly();
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getCorrelationKeys(): array
    {
        return $this->correlationKeys;
    }

    public function getLocalCorrelationKeys(): array
    {
        return $this->localCorrelationKeys;
    }

    public function getProcessInstanceId(): string
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
