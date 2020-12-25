<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\ItemKind;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ItemDefinitionInterface,
    RootElementInterface
};

class ItemDefinitionImpl extends RootElementImpl implements ItemDefinitionInterface
{
    protected static $structureRefAttribute;
    protected static $isCollectionAttribute;
    protected static $itemKindAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ItemDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ITEM_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ItemDefinitionImpl($instanceContext);
                }
            }
        );

        self::$structureRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_STRUCTURE_REF)
        ->build();

        self::$isCollectionAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_COLLECTION)
        ->defaultValue(false)
        ->build();

        self::$itemKindAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ITEM_KIND,
            ItemKind::class
        )
        ->defaultValue(ItemKind::INFORMATION)
        ->build();

        $typeBuilder->build();
    }

    public function getStructureRef(): string
    {
        return self::$structureRefAttribute->getValue($this);
    }

    public function setStructureRef(string $structureRef): void
    {
        self::$structureRefAttribute->setValue($this, $structureRef);
    }

    public function isCollection(): bool
    {
        return self::$isCollectionAttribute->getValue($this);
    }

    public function setCollection(bool $isCollection): void
    {
        self::$isCollectionAttribute->setValue($this, $isCollection);
    }

    public function getItemKind(): string
    {
        return self::$itemKindAttribute->getValue($this);
    }

    public function setItemKin(string $itemKind): void
    {
        self::$itemKindAttribute->setValue($this, $itemKind);
    }
}
