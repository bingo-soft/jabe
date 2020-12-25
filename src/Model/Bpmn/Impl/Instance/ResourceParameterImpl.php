<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ExpressionInterface,
    ItemDefinitionInterface,
    ResourceParameterInterface
};

class ResourceParameterImpl extends BaseElementImpl implements ResourceParameterInterface
{
    protected static $nameAttribute;
    protected static $typeAttribute;
    protected static $isRequiredAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ResourceParameterInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RESOURCE_PARAMETER
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ResourceParameterImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$typeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TYPE)
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        self::$isRequiredAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_REQUIRED)
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

    public function getType(): ItemDefinitionInterface
    {
        return self::$typeAttribute->getReferenceTargetElement($this);
    }

    public function setType(ItemDefinitionInterface $type): void
    {
        self::$typeAttribute->setReferenceTargetElement($this, $type);
    }

    public function isRequired(): bool
    {
        return self::$isRequiredAttribute->getValue($this);
    }

    public function setRequired(bool $isRequired): void
    {
        self::$isRequiredAttribute->setValue($this, $isRequired);
    }
}
