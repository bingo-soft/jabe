<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    DataObjectReferenceInterface,
    DataObjectInterface,
    DataStateInterface,
    FlowElementInterface,
    ItemDefinitionInterface
};

class DataObjectReferenceImpl extends FlowElementImpl implements DataObjectReferenceInterface
{
    protected static $itemSubjectRefAttribute;
    protected static $dataObjectRefAttribute;
    protected static $dataStateChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataObjectReferenceInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_OBJECT_REFERENCE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataObjectReferenceImpl($instanceContext);
                }
            }
        );

        self::$itemSubjectRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ITEM_SUBJECT_REF
        )
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        self::$dataObjectRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_DATA_OBJECT_REF
        )
        ->idAttributeReference(DataObjectInterface::class)
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

    public function getDataObject(): DataObjectInterface
    {
        return self::$dataObjectRefAttribute->getReferenceTargetElement($this);
    }

    public function setDataObject(DataObjectInterface $dataObject): void
    {
        self::$dataObjectRefAttribute->setReferenceTargetElement($this, $dataObject);
    }
}
