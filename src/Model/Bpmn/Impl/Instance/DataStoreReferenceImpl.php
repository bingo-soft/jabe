<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataStoreReferenceInterface,
    DataStoreInterface,
    DataStateInterface,
    FlowElementInterface,
    ItemDefinitionInterface
};

class DataStoreReferenceImpl extends FlowElementImpl implements DataStoreReferenceInterface
{
    protected static $itemSubjectRefAttribute;
    protected static $dataStoreRefAttribute;
    protected static $dataStateChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataStoreReferenceInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_STORE_REFERENCE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataStoreReferenceImpl($instanceContext);
                }
            }
        );

        self::$itemSubjectRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ITEM_SUBJECT_REF
        )
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        self::$dataStoreRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_DATA_STORE_REF
        )
        ->idAttributeReference(DataStoreInterface::class)
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

    public function getDataStore(): DataStoreInterface
    {
        return self::$dataStoreRefAttribute->getReferenceTargetElement($this);
    }

    public function setDataStore(DataStoreInterface $dataStore): void
    {
        self::$dataStoreRefAttribute->setReferenceTargetElement($this, $dataStore);
    }
}
