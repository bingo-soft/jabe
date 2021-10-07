<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ErrorEventDefinitionInterface;

class ExtensionErrorEventDefinitionBuilder extends AbstractErrorEventDefinitionBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ErrorEventDefinitionInterface $element
    ) {
        parent::__construct($modelInstance, $element, ExtensionErrorEventDefinitionBuilder::class);
    }

    public function expression(string $expression): ExtensionErrorEventDefinitionBuilder
    {
        $this->element->setAttributeValue("expression", $expression);
        return $this;
    }

    public function errorEventDefinitionDone(): AbstractServiceTaskBuilder
    {
        return $this->element->getParentElement()->getParentElement()->builder();
    }
}
