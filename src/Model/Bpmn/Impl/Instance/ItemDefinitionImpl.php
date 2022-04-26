<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\ItemKind;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            new class implements ModelTypeInstanceProviderInterface
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

    public function setItemKind(string $itemKind): void
    {
        self::$itemKindAttribute->setValue($this, $itemKind);
    }
}
