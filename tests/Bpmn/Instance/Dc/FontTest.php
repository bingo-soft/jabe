<?php

namespace Tests\Bpmn\Instance\Dc;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Tests\Bpmn\Instance\{
    BpmnModelElementInstanceTest,
    BpmnTypeAssumption
};

class FontTest extends BpmnModelElementInstanceTest
{
    protected $namespace = __NAMESPACE__;

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, BpmnModelConstants::DC_NS);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name"),
            new AttributeAssumption(null, "size"),
            new AttributeAssumption(null, "isBold"),
            new AttributeAssumption(null, "isItalic"),
            new AttributeAssumption(null, "isUnderline"),
            new AttributeAssumption(null, "isStrikeThrough")
        ];
    }
}
