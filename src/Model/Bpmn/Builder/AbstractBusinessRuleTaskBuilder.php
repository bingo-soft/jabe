<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BusinessRuleInterface
};

abstract class AbstractBusinessRuleTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BusinessRuleInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function implementation(string $implementation): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setImplementation($implementation);
        return $this;
    }

    public function setClass(string $className): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setClass($className);
        return $this;
    }

    public function expression(string $expression): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setExpression($expression);
        return $this;
    }

    public function delegateExpression(string $delegateExpression): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setExpression($delegateExpression);
        return $this;
    }

    public function resultVariable(string $resultVariable): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setResultVariable($delegateExpression);
        return $this;
    }

    public function topic(string $topic): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setTopic($topic);
        return $this;
    }

    public function type(string $type): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setType($type);
        return $this;
    }

    public function decisionRef(string $decisionRef): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setDecisionRef($decisionRef);
        return $this;
    }

    public function decisionRefBinding(string $decisionRefBinding): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setDecisionRefBinding($decisionRefBinding);
        return $this;
    }

    public function decisionRefVersion(string $decisionRefVersion): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setDecisionRefVersion($decisionRefVersion);
        return $this;
    }

    public function decisionRefVersionTag(string $decisionRefVersionTag): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setDecisionRefVersionTag($decisionRefVersionTag);
        return $this;
    }

    public function decisionRefTenantId(string $decisionRefTenantId): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setDecisionRefTenantId($decisionRefTenantId);
        return $this;
    }

    public function mapDecisionResult(string $mapDecisionResult): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setMapDecisionResult($mapDecisionResult);
        return $this;
    }

    public function mapTaskPriority(string $taskPriority): AbstractBusinessRuleTaskBuilder
    {
        $this->element->setTaskPriority($taskPriority);
        return $this;
    }
}
