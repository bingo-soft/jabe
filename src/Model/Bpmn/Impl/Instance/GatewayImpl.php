<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\GatewayDirection;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    GatewayInterface,
    FlowNodeInterface
};
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

abstract class GatewayImpl extends FlowNodeImpl implements GatewayInterface
{
    protected static $gatewayDirectionAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(GatewayInterface::class, BpmnModelConstants::BPMN_ELEMENT_GATEWAY)
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowNodeInterface::class)
        ->abstractType();

        self::$gatewayDirectionAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_GATEWAY_DIRECTION,
            GatewayDirection::class
        )
        ->defaultValue(GatewayDirection::UNSPECIFIED)
        ->build();

        $typeBuilder->build();
    }

    public function getGatewayDirection(): string
    {
        return self::$gatewayDirectionAttribute->getValue($this);
    }

    public function setGatewayDirection(string $gatewayDirection): void
    {
        self::$gatewayDirectionAttribute->setValue($this, $gatewayDirection);
    }

    public function getDiagramElement(): BpmnShapeInterface
    {
        return parent::getDiagramElement();
    }
}
