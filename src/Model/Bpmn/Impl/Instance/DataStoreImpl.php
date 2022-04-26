<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataStateInterface,
    DataStoreInterface,
    ItemDefinitionInterface,
    RootElementInterface
};

class DataStoreImpl extends RootElementImpl implements DataStoreInterface
{
    protected static $nameAttribute;
    protected static $capacityAttribute;
    protected static $isUnlimitedAttribute;
    protected static $itemSubjectRefAttribute;
    protected static $dataStateChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataStoreInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_STORE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataStoreImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$capacityAttribute = $typeBuilder->integerAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_CAPACITY)
        ->build();

        self::$isUnlimitedAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_UNLIMITED)
        ->defaultValue(true)
        ->build();

        self::$itemSubjectRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ITEM_SUBJECT_REF
        )
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataStateChild = $sequenceBuilder->element(DataStateInterface::class)
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

    public function getCapacity(): int
    {
        return self::$capacityAttribute->getValue($this);
    }

    public function setCapacity(int $capacity): void
    {
        self::$capacityAttribute->setValue($this, $capacity);
    }

    public function isUnlimited(): bool
    {
        return self::$isUnlimitedAttribute->getValue($this);
    }

    public function setUnlimited(bool $isUnlimited): void
    {
        self::$isUnlimitedAttribute->setValue($this, $isUnlimited);
    }

    public function getItemSubject(): ItemDefinitionInterface
    {
        return self::$itemSubjectRefAttribute->getReferenceTargetElement($this);
    }

    public function setItemSubject(ItemDefinitionInterface $itemSubject): void
    {
        self::$itemSubjectRefAttribute->setReferenceTargetElement($this, $itemSubject);
    }

    public function getDataState(): DataStateInterface
    {
        return self::$dataStateChild->getChild($this);
    }

    public function setDataState(DataStateInterface $dataState): void
    {
        self::$dataStateChild->setChild($this, $dataState);
    }
}
