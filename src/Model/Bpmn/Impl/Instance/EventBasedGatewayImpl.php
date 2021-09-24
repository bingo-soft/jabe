<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\EventBasedGatewayBuilder;
use BpmPlatform\Model\Bpmn\EventBasedGatewayType;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    EventBasedGatewayInterface,
    GatewayInterface
};

class EventBasedGatewayImpl extends GatewayImpl implements EventBasedGatewayInterface
{
    protected static $instantiateAttribute;
    protected static $eventGatewayTypeAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EventBasedGatewayInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_EVENT_BASED_GATEWAY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(GatewayInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new EventBasedGatewayImpl($instanceContext);
                }
            }
        );

        self::$instantiateAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_INSTANTIATE)
        ->defaultValue(false)
        ->build();

        self::$eventGatewayTypeAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_EVENT_GATEWAY_TYPE,
            EventBasedGatewayType::class
        )
        ->defaultValue(EventBasedGatewayType::EXCLUSIVE)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): EventBasedGatewayBuilder
    {
        return new EventBasedGatewayBuilder($this->modelInstance, $this);
    }

    public function isInstantiate(): bool
    {
        return self::$instantiateAttribute->getValue($this);
    }

    public function setInstantiate(bool $isInstantiate): void
    {
        self::$instantiateAttribute->setValue($this, $isInstantiate);
    }

    public function getEventGatewayType(): string
    {
        return self::$eventGatewayTypeAttribute->getValue($this);
    }

    public function setEventGatewayType(string $eventGatewayType): void
    {
        self::$eventGatewayTypeAttribute->setValue($this, $eventGatewayType);
    }

    public function isAsyncAfter(): bool
    {
        throw new \Exception("'asyncAfter' is not supported for 'Event Based Gateway'");
    }

    public function setAsyncAfter(bool $isAsyncAfter): void
    {
        throw new \Exception("'asyncAfter' is not supported for 'Event Based Gateway'");
    }
}
