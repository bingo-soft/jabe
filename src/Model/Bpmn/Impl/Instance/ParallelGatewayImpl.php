<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Builder\ParallelGatewayBuilder;
use Jabe\Model\Bpmn\ParallelGatewayType;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ParallelGatewayImpl($instanceContext);
                }
            }
        );

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ParallelGatewayBuilder
    {
        return new ParallelGatewayBuilder($this->modelInstance, $this);
    }

    public function isAsync(): bool
    {
        return self::$asyncAttribute->getValue($this);
    }

    public function setAsync(bool $isAsync): void
    {
        self::$asyncAttribute->setValue($this, $isAsync);
    }
}
