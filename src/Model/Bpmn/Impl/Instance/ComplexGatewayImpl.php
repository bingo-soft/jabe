<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\ComplexGatewayBuilder;
use BpmPlatform\Model\Bpmn\ComplexGatewayType;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivationConditionInterface,
    ComplexGatewayInterface,
    GatewayInterface,
    SequenceFlowInterface
};

class ComplexGatewayImpl extends GatewayImpl implements ComplexGatewayInterface
{
    protected static $defaultAttribute;
    protected static $activationConditionChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ComplexGatewayInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_COMPLEX_GATEWAY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(GatewayInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ComplexGatewayImpl($instanceContext);
                }
            }
        );

        self::$defaultAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_DEFAULT)
        ->idAttributeReference(SequenceFlowInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$activationConditionChild = $sequenceBuilder->element(ActivationConditionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ComplexGatewayBuilder
    {
        return new ComplexGatewayBuilder($this->modelInstance, $this);
    }

    public function getDefault(): SequenceFlowInterface
    {
        return self::$defaultAttribute->getReferenceTargetElement($this);
    }

    public function setDefault(SequenceFlowInterface $defaultFlow): void
    {
        self::$defaultAttribute->setReferenceTargetElement($this, $defaultFlow);
    }

    public function getActivationCondition(): ActivationConditionInterface
    {
        return self::$activationConditionChild->getChild($this);
    }

    public function setActivationCondition(ActivationConditionInterface $activationCondition): void
    {
        self::$activationConditionChild->setChild($this, $activationCondition);
    }
}
