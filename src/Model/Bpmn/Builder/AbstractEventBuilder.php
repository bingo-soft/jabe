<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    EventInterface,
    InputOutputInterface,
    InputParameterInterface,
    OutputParameterInterface
};

abstract class AbstractEventBuilder extends AbstractFlowNodeBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function inputParameter(string $name, string $value): AbstractEventBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, InputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this->myself;
    }

    public function outputParameter(string $name, string $value): AbstractEventBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, OutputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this->myself;
    }
}
