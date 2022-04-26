<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
    MessageInterface,
    OperationInterface,
    SendTaskInterface
};

abstract class AbstractSendTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SendTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function implementation(string $implementation): AbstractSendTaskBuilder
    {
        $this->element->setImplementation($implementation);
        return $this;
    }

    /**
     * @param mixed $message
     */
    public function message($message): AbstractSendTaskBuilder
    {
        if (is_string($message)) {
            $message = $this->findMessageForName($message);
        }
        $this->element->setMessage($message);
        return $this;
    }

    public function operation(OperationInterface $operation): AbstractSendTaskBuilder
    {
        $this->element->setOperation($operation);
        return $this;
    }

    public function setClass(string $className): AbstractSendTaskBuilder
    {
        $this->element->setClass($className);
        return $this;
    }

    public function expression(string $expression): AbstractSendTaskBuilder
    {
        $this->element->setExpression($expression);
        return $this;
    }

    public function delegateExpression(string $expression): AbstractSendTaskBuilder
    {
        $this->element->setDelegateExpression($expression);
        return $this;
    }

    public function resultVariable(string $resultVariable): AbstractSendTaskBuilder
    {
        $this->element->setResultVariable($resultVariable);
        return $this;
    }

    public function topic(string $topic): AbstractSendTaskBuilder
    {
        $this->element->setTopic($topic);
        return $this;
    }

    public function type(string $type): AbstractSendTaskBuilder
    {
        $this->element->setType($type);
        return $this;
    }

    public function taskPriority(string $taskPriority): AbstractSendTaskBuilder
    {
        $this->element->setTaskPriority($taskPriority);
        return $this;
    }
}
