<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Builder\InclusiveGatewayBuilder;
use Jabe\Model\Bpmn\InclusiveGatewayType;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    InclusiveGatewayInterface,
    GatewayInterface,
    SequenceFlowInterface
};

class InclusiveGatewayImpl extends GatewayImpl implements InclusiveGatewayInterface
{
    protected static $defaultAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InclusiveGatewayInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_INCLUSIVE_GATEWAY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(GatewayInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new InclusiveGatewayImpl($instanceContext);
                }
            }
        );

        self::$defaultAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_DEFAULT)
        ->idAttributeReference(SequenceFlowInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): InclusiveGatewayBuilder
    {
        return new InclusiveGatewayBuilder($this->modelInstance, $this);
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
