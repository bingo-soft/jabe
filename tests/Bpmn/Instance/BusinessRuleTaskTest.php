<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\TaskInterface;

class BusinessRuleTaskTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, TaskInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "implementation", false, false, "##unspecified"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "class"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "delegateExpression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "expression"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "resultVariable"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "topic"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "type"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "decisionRef"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "decisionRefBinding"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "decisionRefVersion"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "decisionRefVersionTag"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "decisionRefTenantId"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "mapDecisionResult"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "taskPriority")
        ];
    }
}
