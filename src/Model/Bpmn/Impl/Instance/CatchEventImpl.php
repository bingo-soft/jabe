<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    CatchEventInterface,
    DataOutputInterface,
    DataOutputAssociationInterface,
    EventDefinitionInterface,
    EventInterface,
    OutputSetInterface
};

abstract class CatchEventImpl extends EventImpl implements CatchEventInterface
{
    protected static $parallelMultipleAttribute;
    protected static $dataOutputCollection;
    protected static $dataOutputAssociationCollection;
    protected static $outputSetChild;
    protected static $eventDefinitionCollection;
    protected static $eventDefinitionRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CatchEventInterface::class,
            BpmnModelConstants:: BPMN_ELEMENT_CATCH_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventInterface::class)
        ->abstractType();

        self::$parallelMultipleAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_PARALLEL_MULTIPLE
        )
        ->defaultValue(false)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataOutputCollection = $sequenceBuilder->elementCollection(
            DataOutputInterface::class
        )
        ->build();

        self::$dataOutputAssociationCollection = $sequenceBuilder->elementCollection(
            DataOutputAssociationInterface::class
        )
        ->build();

        self::$outputSetChild = $sequenceBuilder->element(OutputSetInterface::class)
        ->build();

        self::$eventDefinitionCollection = $sequenceBuilder->elementCollection(EventDefinitionInterface::class)
        ->build();

        self::$eventDefinitionRefCollection = $sequenceBuilder->elementCollection(EventDefinitionRef::class)
        ->qNameElementReferenceCollection(EventDefinitionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function isParallelMultiple(): bool
    {
        return self::$parallelMultipleAttribute->getValue($this);
    }

    public function setParallelMultiple(bool $parallelMultiple): void
    {
        self::$parallelMultipleAttribute->setValue($this, $parallelMultiple);
    }

    public function getDataOutputs(): array
    {
        return self::$dataOutputCollection->get($this);
    }

    public function getDataOutputAssociations(): array
    {
        return self::$dataOutputAssociationCollection->get($this);
    }

    public function getOutputSet(): OutputSetInterface
    {
        return self::$outputSetChild->getChild($this);
    }

    public function setOutputSet(OutputSetInterface $outputSet): void
    {
        self::$outputSetChild->setChild($this, $outputSet);
    }

    public function getEventDefinitions(): array
    {
        return self::$eventDefinitionCollection->get($this);
    }

    public function addEventDefinition(EventDefinitionInterface $eventDefinition): void
    {
        self::$eventDefinitionCollection->add($this, $eventDefinition);
    }

    public function getEventDefinitionRefs(): array
    {
        return self::$eventDefinitionRefCollection->getReferenceTargetElements($this);
    }
}
