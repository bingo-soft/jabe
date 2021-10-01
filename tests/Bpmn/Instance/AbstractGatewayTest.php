<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\{
    Bpmn,
    GatewayDirection
};
use BpmPlatform\Model\Bpmn\Impl\{
    BpmnModelConstants,
    QueryImpl
};
use BpmPlatform\Model\Bpmn\Instance\GatewayInterface;
use BpmPlatform\Model\Xml\Impl\Util\ReflectUtil;

abstract class AbstractGatewayTest extends BpmnModelElementInstanceTest
{
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $inputStream = ReflectUtil::getResourceAsFile("tests/Bpmn/Resources/GatewaysTest.xml");
        $elementInstances = Bpmn::getInstance()->readModelFromStream($inputStream)->getModelElementsByType(
            $this->modelElementType
        );
        $this->assertCount(1, $elementInstances);
        $this->gateway = $elementInstances[0];
        $this->assertEquals(GatewayDirection::MIXED, $this->gateway->getGatewayDirection());
    }

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, GatewayInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [ ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncBefore", false, false, false),
            new AttributeAssumption(BpmnModelConstants::EXTENSION_NS, "asyncAfter", false, false, false)
        ];
    }
}
