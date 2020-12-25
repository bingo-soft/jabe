<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    DataObjectInterface,
    DataStateInterface,
    FlowElementInterface,
    ItemDefinitionInterface
};

class DataObjectImpl extends FlowElementImpl implements DataObjectInterface
{
    protected static $itemSubjectRefAttribute;
    protected static $isCollectionAttribute;
    protected static $dataStateChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataObjectInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_OBJECT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataObjectImpl($instanceContext);
                }
            }
        );

        self::$itemSubjectRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ITEM_SUBJECT_REF
        )
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        self::$isCollectionAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_COLLECTION)
        ->defaultValue(false)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataStateChild = $sequenceBuilder->element(DataStateInterface::class)
        ->build();

        $typeBuilder->build();
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

    public function isCollection(): bool
    {
        return self::$isCollectionAttribute->getValue($this);
    }

    public function setCollection(bool $isCollection): void
    {
        self::$isCollectionAttribute->setValue($this, $isCollection);
    }
}
