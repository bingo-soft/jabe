<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    FlowNodeInterface,
    LaneInterface
};

class LaneImpl extends BaseElementImpl implements LaneInterface
{
    protected static $nameAttribute;
    protected static $partitionElementRefAttribute;
    protected static $partitionElementChild;
    protected static $flowNodeRefCollection;
    protected static $childLaneSetChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LaneInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_LANE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new LaneImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$partitionElementRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_PARTITION_ELEMENT_REF
        )
        ->qNameAttributeReference(PartitionElement::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$partitionElementChild = $sequenceBuilder->element(PartitionElement::class)
        ->build();

        self::$flowNodeRefCollection = $sequenceBuilder->elementCollection(FlowNodeRef::class)
        ->idElementReferenceCollection(FlowNodeInterface::class)
        ->build();

        self::$childLaneSetChild = $sequenceBuilder->element(ChildLaneSet::class)
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

    public function getPartitionElement(): PartitionElement
    {
        return self::$partitionElementRefAttribute->getReferenceTargetElement($this);
    }

    public function setPartitionElement(PartitionElement $partitionElement): void
    {
        self::$partitionElementRefAttribute->setReferenceTargetElement($this, $partitionElement);
    }

    public function getPartitionElementChild(): PartitionElement
    {
        return self::$partitionElementChild->getChild($this);
    }

    public function setPartitionElementChild(PartitionElement $partitionElement): void
    {
        self::$partitionElementChild->setChild($this, $partitionElement);
    }

    public function getFlowNodeRefs(): array
    {
        return self::$flowNodeRefCollection->getReferenceTargetElements($this);
    }

    public function getChildLaneSet(): ChildLaneSet
    {
        return self::$childLaneSetChild->getChild($this);
    }

    public function setChildLaneSet(ChildLaneSet $childLaneSet): void
    {
        self::$childLaneSetChild->setChild($this, $childLaneSet);
    }
}
