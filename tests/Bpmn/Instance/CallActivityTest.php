<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\ActivityInterface;

class CallActivityTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, ActivityInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "calledElement"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "async", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "calledElementBinding"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "calledElementVersion"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "calledElementVersionTag"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "calledElementTenantId"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "caseRef"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "caseBinding"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "caseVersion"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "caseTenantId"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "variableMappingClass"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "variableMappingDelegateExpression")
        ];
    }
}
