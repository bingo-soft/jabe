<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ExpressionInterface,
    ResourceParameterBindingInterface,
    ResourceParameterInterface
};

class ResourceParameterBindingImpl extends BaseElementImpl implements ResourceParameterBindingInterface
{
    protected static $parameterRefAttribute;
    protected static $expressionChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ResourceParameterBindingInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RESOURCE_PARAMETER_BINDING
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ResourceParameterBindingImpl($instanceContext);
                }
            }
        );

        self::$parameterRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_PARAMETER_REF)
        ->required()
        ->qNameAttributeReference(ResourceParameterInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$expressionChild = $sequenceBuilder->element(ExpressionInterface::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getParameter(): ResourceParameterInterface
    {
        return self::$parameterRefAttribute->getReferenceTargetElement($this);
    }

    public function setParameter(ResourceParameterInterface $parameter): void
    {
        self::$parameterRefAttribute->setReferenceTargetElement($this, $parameter);
    }

    public function getExpression(): ExpressionInterface
    {
        return self::$expressionChild->getChild($this);
    }

    public function setExpression(ExpressionInterface $expression): void
    {
        self::$expressionChild->setChild($this, $expression);
    }
}
