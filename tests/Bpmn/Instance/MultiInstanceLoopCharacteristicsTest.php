<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\{
    LoopDataInputRef,
    LoopDataOutputRef
};
use BpmPlatform\Model\Bpmn\Instance\{
    LoopCharacteristicsInterface,
    LoopCardinalityInterface,
    OutputDataItemInterface,
    InputDataItemInterface,
    ComplexBehaviorDefinitionInterface,
    CompletionConditionInterface
};

class MultiInstanceLoopCharacteristicsTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, LoopCharacteristicsInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, LoopCardinalityInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, LoopDataInputRef::class, 0, 1),
            new BpmnChildElementAssumption($this->model, LoopDataOutputRef::class, 0, 1),
            new BpmnChildElementAssumption($this->model, OutputDataItemInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, InputDataItemInterface::class, 0, 1),
            new BpmnChildElementAssumption($this->model, ComplexBehaviorDefinitionInterface::class),
            new BpmnChildElementAssumption($this->model, CompletionConditionInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "isSequential", false, false, false),
            new AttributeAssumption(null, "behavior", false, false, "All"),
            new AttributeAssumption(null, "oneBehaviorEventRef"),
            new AttributeAssumption(null, "noneBehaviorEventRef"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncBefore", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncAfter", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "exclusive", false, false, true),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "collection"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "elementVariable")
        ];
    }
}
