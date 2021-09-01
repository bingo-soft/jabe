<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\MessageEventDefinitionInterface;

abstract class AbstractMessageEventDefinitionBuilder extends AbstractRootElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MessageEventDefinitionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function id(string $identifier): AbstractMessageEventDefinitionBuilder
    {
        return parent::id($identifier);
    }

    public function message(string $message): AbstractMessageEventDefinitionBuilder
    {
        $this->element->setMessage($this->findMessageForName($message));
        return $this;
    }

    public function topic(string $topic): AbstractMessageEventDefinitionBuilder
    {
        $this->element->setTopic($topic);
        return $this;
    }

    public function type(string $type): AbstractMessageEventDefinitionBuilder
    {
        $this->element->setType($type);
        return $this;
    }

    public function taskPriority(string $taskPriority): AbstractMessageEventDefinitionBuilder
    {
        $this->element->setTaskPriority($taskPriority);
        return $this;
    }

    public function messageEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
