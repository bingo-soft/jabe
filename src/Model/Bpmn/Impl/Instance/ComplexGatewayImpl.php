<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Builder\ComplexGatewayBuilder;
use Jabe\Model\Bpmn\ComplexGatewayType;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
