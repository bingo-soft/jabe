<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\ParallelGatewayBuilder;
use BpmPlatform\Model\Bpmn\ParallelGatewayType;
use BpmPlatform\Model\Bpmn\Instance\{
    ParallelGatewayInterface,
    GatewayInterface,
    SequenceFlowInterface
};

class ParallelGatewayImpl extends GatewayImpl implements ParallelGatewayInterface
{
    protected static $asyncAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ParallelGatewayInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_PARALLEL_GATEWAY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(GatewayInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ParallelGatewayImpl($instanceContext);
                }
            }
        );

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ParallelGatewayBuilder
    {
        return new ParallelGatewayBuilder($this->modelInstance, $this);
    }
}