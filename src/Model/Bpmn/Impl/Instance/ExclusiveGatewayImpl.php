<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\ExclusiveGatewayBuilder;
use BpmPlatform\Model\Bpmn\ExclusiveGatewayType;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ExclusiveGatewayInterface,
    GatewayInterface,
    SequenceFlowInterface
};

class ExclusiveGatewayImpl extends GatewayImpl implements ExclusiveGatewayInterface
{
    protected static $defaultAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ExclusiveGatewayInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_EXCLUSIVE_GATEWAY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(GatewayInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ExclusiveGatewayImpl($instanceContext);
                }
            }
        );

        self::$defaultAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_DEFAULT)
        ->idAttributeReference(SequenceFlowInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ExclusiveGatewayBuilder
    {
        return new ExclusiveGatewayBuilder($this->modelInstance, $this);
    }

    public function getDefault(): SequenceFlowInterface
    {
        return self::$defaultAttribute->getReferenceTargetElement($this);
    }

    public function setDefault(SequenceFlowInterface $defaultFlow): void
    {
        self::$defaultAttribute->setReferenceTargetElement($this, $defaultFlow);
    }
}
