<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\GatewayDirection;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    GatewayInterface,
    FlowNodeInterface
};
use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\Builder\AbstractGatewayBuilder;
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

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

    public function builder(): AbstractGatewayBuilder
    {
        throw new BpmnModelException("No builder implemented");
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
