<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\RelationshipDirection;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    RelationshipInterface
};

class RelationshipImpl extends BaseElementImpl implements RelationshipInterface
{
    protected static $typeAttribute;
    protected static $directionAttribute;
    protected static $sourceCollection;
    protected static $targetCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            RelationshipInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RELATIONSHIP
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new RelationshipImpl($instanceContext);
                }
            }
        );

        $typeAttribute = $typeBuilder->stringAttribute(BaseElementInterface::BPMN_ATTRIBUTE_TYPE)
        ->required()
        ->build();

        $directionAttribute = $typeBuilder->enumAttribute(
            BaseElementInterface::BPMN_ATTRIBUTE_DIRECTION,
            RelationshipDirection::class
        )
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        $sourceCollection = $sequenceBuilder->elementCollection(Source::class)
        ->minOccurs(1)
        ->build();

        $targetCollection = $sequenceBuilder->elementCollection(Target::class)
        ->minOccurs(1)
        ->build();

        $typeBuilder->build();
    }

    public function getType(): string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function getDirection(): string
    {
        return self::$directionAttribute->getValue($this);
    }

    public function setDirection(string $direction): void
    {
        self::$directionAttribute->setValue($this, $direction);
    }

    public function getSources(): array
    {
        return self::$sourceCollection->get($this);
    }

    public function getTargets(): array
    {
        return self::$targetCollection->get($this);
    }
}
