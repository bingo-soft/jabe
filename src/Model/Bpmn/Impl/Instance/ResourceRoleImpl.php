<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    ResourceInterface,
    ResourceAssignmentExpressionInterface,
    ResourceParameterBindingInterface,
    ResourceRoleInterface
};

class ResourceRoleImpl extends BaseElementImpl implements ResourceRoleInterface
{
    protected static $nameAttribute;
    protected static $resourceRefChild;
    protected static $resourceParameterBindingCollection;
    protected static $resourceAssignmentExpressionChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ResourceRoleInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RESOURCE_ROLE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ResourceRoleImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$resourceRefChild = $sequenceBuilder->element(ResourceRef::class)
        ->qNameElementReference(ResourceInterface::class)
        ->build();

        self::$resourceParameterBindingCollection = $sequenceBuilder->elementCollection(
            ResourceParameterBindingInterface::class
        )
        ->build();

        self::$resourceAssignmentExpressionChild = $sequenceBuilder->element(
            ResourceAssignmentExpressionInterface::class
        )
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getResource(): ?ResourceInterface
    {
        return self::$resourceRefChild->getReferenceTargetElement($this);
    }

    public function setResource(ResourceInterface $resource): void
    {
        self::$resourceRefChild->setReferenceTargetElement($this, $resource);
    }

    public function getResourceParameterBinding(): array
    {
        return self::$resourceParameterBindingCollection->get($this);
    }

    public function getResourceAssignmentExpression(): ResourceAssignmentExpressionInterface
    {
        return self::$resourceAssignmentExpressionChild->getChild($this);
    }
}
