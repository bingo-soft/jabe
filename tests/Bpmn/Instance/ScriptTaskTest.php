<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    ScriptInterface,
    TaskInterface
};

class ScriptTaskTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, TaskInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ScriptInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "scriptFormat"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "resultVariable"),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "resource")
        ];
    }
}
