<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ServiceTaskInterface
};

abstract class AbstractServiceTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ServiceTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function implementation(string $implementation): AbstractServiceTaskBuilder
    {
        $this->element->setImplementation($implementation);
        return $this;
    }

    public function setClass(string $className): AbstractServiceTaskBuilder
    {
        $this->element->setClass($className);
        return $this;
    }

    public function expression(string $expression): AbstractServiceTaskBuilder
    {
        $this->element->setExpression($expression);
        return $this;
    }

    public function delegateExpression(string $expression): AbstractServiceTaskBuilder
    {
        $this->element->setDelegateExpression($expression);
        return $this;
    }

    public function resultVariable(string $resultVariable): AbstractServiceTaskBuilder
    {
        $this->element->setResultVariable($resultVariable);
        return $this;
    }

    public function topic(string $topic): AbstractServiceTaskBuilder
    {
        $this->element->setTopic($topic);
        return $this;
    }

    public function type(string $type): AbstractServiceTaskBuilder
    {
        $this->element->setType($type);
        return $this;
    }

    public function externalTask(string $topic): AbstractServiceTaskBuilder
    {
        $this->type("external");
        $this->topic($topic);
        return $this;
    }

    public function taskPriority(string $taskPriority): AbstractServiceTaskBuilder
    {
        $this->element->taskPriority($taskPriority);
        return $this;
    }
}
