<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    CorrelationPropertyInterface,
    CorrelationPropertyRetrievalExpressionInterface,
    ItemDefinitionInterface,
    RootElementInterface
};

class CorrelationPropertyImpl extends RootElementImpl implements CorrelationPropertyInterface
{
    protected static $nameAttribute;
    protected static $typeAttribute;
    protected static $correlationPropertyRetrievalExpressionCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CorrelationPropertyInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CORRELATION_PROPERTY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CorrelationPropertyImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$typeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TYPE)
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$correlationPropertyRetrievalExpressionCollection = $sequenceBuilder
        ->elementCollection(CorrelationPropertyRetrievalExpressionInterface::class)
        ->required()
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

    public function getType(): string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function getCorrelationPropertyRetrievalExpressions(): array
    {
        return self::$correlationPropertyRetrievalExpressionCollection->get($this);
    }
}
