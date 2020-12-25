<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\{
    ActivityInterface,
    DataInputAssociationInterface,
    DataOutputAssociationInterface,
    FlowNodeInterface,
    IoSpecificationInterface,
    LoopCharacteristicsInterface,
    PropertyInterface,
    SequenceFlowInterface
};

abstract class ActivityImpl extends FlowNodeImpl implements ActivityInterface
{
    protected static $isForCompensationAttribute;
    protected static $startQuantityAttribute;
    protected static $completionQuantityAttribute;
    protected static $defaultAttribute;
    protected static $ioSpecificationChild;
    protected static $propertyCollection;
    protected static $dataInputAssociationCollection;
    protected static $dataOutputAssociationCollection;
    protected static $resourceRoleCollection;
    protected static $loopCharacteristicsChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(ActivityInterface::class, BpmnModelConstants::BPMN_ELEMENT_ACTIVITY)
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowNodeInterface::class)
        ->abstractType();

        self::$isForCompensationAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_IS_FOR_COMPENSATION
        )
        ->defaultValue(false)
        ->build();

        self::$startQuantityAttribute = $typeBuilder->integerAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_START_QUANTITY
        )
        ->defaultValue(1)
        ->build();

        self::$completionQuantityAttribute = $typeBuilder->integerAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_COMPLETION_QUANTITY
        )
        ->defaultValue(1)
        ->build();

        self::$defaultAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_DEFAULT)
        ->idAttributeReference(SequenceFlowInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$ioSpecificationChild = $sequenceBuilder->element(IoSpecificationInterface::class)
        ->build();

        self::$propertyCollection = $sequenceBuilder->elementCollection(PropertyInterface::class)
        ->build();

        self::$dataInputAssociationCollection = $sequenceBuilder->elementCollection(
            DataInputAssociationInterface::class
        )
        ->build();

        self::$dataOutputAssociationCollection = $sequenceBuilder->elementCollection(
            DataOutputAssociationInterface::class
        )
        ->build();

        self::$resourceRoleCollection = $sequenceBuilder->elementCollection(ResourceRoleInterface::class)
        ->build();

        self::$loopCharacteristicsChild = $sequenceBuilder->element(LoopCharacteristicsInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function isForCompensation(): bool
    {
        return self::$isForCompensationAttribute->getValue($this);
    }

    public function setForCompensation(bool $isForCompensation): void
    {
        self::$isForCompensationAttribute->setValue($this, $isForCompensation);
    }

    public function getStartQuantity(): int
    {
        return self::$startQuantityAttribute->getValue($this);
    }

    public function setStartQuantity(int $startQuantity): void
    {
        self::$startQuantityAttribute->setValue($this, $startQuantity);
    }

    public function getCompletionQuantity(): int
    {
        return self::$completionQuantityAttribute->getValue($this);
    }

    public function setCompletionQuantity(int $completionQuantity): void
    {
        self::$completionQuantityAttribute->setValue($this, $completionQuantity);
    }

    public function getDefault(): SequenceFlowInterface
    {
        return self::$defaultAttribute->getReferenceTargetElement($this);
    }

    public function setDefault(SequenceFlowInterface $defaultFlow): void
    {
        self::$defaultAttribute->setReferenceTargetElement($this, $defaultFlow);
    }

    public function getIoSpecification(): IoSpecificationInterface
    {
        return self::$ioSpecificationChild->getChild($this);
    }

    public function setIoSpecification(IoSpecificationInterface $ioSpecification): void
    {
        self::$ioSpecificationChild->setChild($this, $ioSpecification);
    }

    public function getProperties(): array
    {
        return self::$propertyCollection->get($this);
    }

    public function getDataInputAssociations(): array
    {
        return self::$dataInputAssociationCollection->get($this);
    }

    public function getDataOutputAssociations(): array
    {
        return self::$dataOutputAssociationCollection->get($this);
    }

    public function getResourceRoles(): array
    {
        return self::$resourceRoleCollection->get($this);
    }

    public function getLoopCharacteristics(): LoopCharacteristicsInterface
    {
        return self::$loopCharacteristicsChild->getChild($this);
    }

    public function setLoopCharacteristics(LoopCharacteristicsInterface $loopCharacteristics): void
    {
        self::$loopCharacteristicsChild->setChild($this, $loopCharacteristics);
    }
}
