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
        return $this->myself;
    }

    public function setClass(string $className): AbstractServiceTaskBuilder
    {
        $this->element->setClass($className);
        return $this->myself;
    }

    public function expression(string $expression): AbstractServiceTaskBuilder
    {
        $this->element->setExpression($expression);
        return $this->myself;
    }

    public function delegateExpression(string $expression): AbstractServiceTaskBuilder
    {
        $this->element->setDelegateExpression($expression);
        return $this->myself;
    }

    public function resultVariable(string $resultVariable): AbstractServiceTaskBuilder
    {
        $this->element->setResultVariable($resultVariable);
        return $this->myself;
    }

    public function topic(string $topic): AbstractServiceTaskBuilder
    {
        $this->element->setTopic($topic);
        return $this->myself;
    }

    public function type(string $type): AbstractServiceTaskBuilder
    {
        $this->element->setType($type);
        return $this->myself;
    }

    public function externalTask(string $topic): AbstractServiceTaskBuilder
    {
        $this->type("external");
        $this->topic($topic);
        return $this->myself;
    }

    public function taskPriority(string $taskPriority): AbstractServiceTaskBuilder
    {
        $this->element->taskPriority($taskPriority);
        return $this->myself;
    }
}
