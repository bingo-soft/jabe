<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    RenderingInterface,
    TaskInterface
};

class UserTaskTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, TaskInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, RenderingInterface::class)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "implementation", false, false, "##unspecified"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "assignee"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "candidateGroups"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "candidateUsers"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "dueDate"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "followUpDate"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "formHandlerClass"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "formKey"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "priority")
        ];
    }
}
