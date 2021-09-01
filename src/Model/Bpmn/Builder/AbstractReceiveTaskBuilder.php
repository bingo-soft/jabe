<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    MessageInterface,
    OperationInterface,
    ReceiveTaskInterface
};

abstract class AbstractReceiveTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ReceiveTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function implementation(string $implementation): AbstractReceiveTaskBuilder
    {
        $this->element->setImplementation($implementation);
        return $this;
    }

    public function instantiate(): AbstractReceiveTaskBuilder
    {
        $this->element->setInstantiate(true);
        return $this;
    }

    /**
     * @param mixed $message
     */
    public function message($message): AbstractReceiveTaskBuilder
    {
        if (is_string($message)) {
            $message = $this->findMessageForName($message);
        }
        $this->element->setMessage($message);
        return $this;
    }

    public function operation(OperationInterface $operation): AbstractReceiveTaskBuilder
    {
        $this->element->setOperation($operation);
        return $this;
    }
}
