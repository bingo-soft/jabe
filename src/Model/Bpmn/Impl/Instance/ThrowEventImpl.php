<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DataInputInterface,
    DataInputAssociationInterface,
    EventDefinitionInterface,
    EventInterface,
    InputSetInterface,
    ThrowEventInterface
};

abstract class ThrowEventImpl extends EventImpl implements ThrowEventInterface
{
    protected static $dataInputCollection;
    protected static $dataInputAssociationCollection;
    protected static $inputSetChild;
    protected static $eventDefinitionCollection;
    protected static $eventDefinitionRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ThrowEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_THROW_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventInterface::class)
        ->abstractType();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataInputCollection = $sequenceBuilder->elementCollection(DataInputInterface::class)
        ->build();

        self::$dataInputAssociationCollection = $sequenceBuilder->elementCollection(
            DataInputAssociationInterface::class
        )
        ->build();

        self::$inputSetChild = $sequenceBuilder->element(InputSetInterface::class)
        ->build();

        self::$eventDefinitionCollection = $sequenceBuilder->elementCollection(EventDefinitionInterface::class)
        ->build();

        self::$eventDefinitionRefCollection = $sequenceBuilder->elementCollection(EventDefinitionRef::class)
        ->qNameElementReferenceCollection(EventDefinitionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getDataInputs(): array
    {
        return self::$dataInputCollection->get($this);
    }

    public function getDataInputAssociations(): array
    {
        return self::$dataInputAssociationCollection->get($this);
    }

    public function getInputSet(): InputSetInterface
    {
        return self::$inputSetChild->getChild($this);
    }

    public function setInputSet(InputSetInterface $inputSet): void
    {
        self::$inputSetChild->setChild($this, $inputSet);
    }

    public function getEventDefinitions(): array
    {
        return self::$eventDefinitionCollection->get($this);
    }

    public function addEventDefinition(EventDefinitionInterface $eventDefinition): void
    {
        self::$eventDefinitionCollection->add($this, $eventDefinition);
    }

    public function removeEventDefinition(EventDefinitionInterface $eventDefinition): void
    {
        self::$eventDefinitionCollection->remove($this, $eventDefinition);
    }

    public function getEventDefinitionRefs(): array
    {
        return self::$eventDefinitionRefCollection->getReferenceTargetElements($this);
    }

    public function addEventDefinitionRef(EventDefinitionInterface $eventDefinition): void
    {
        self::$eventDefinitionRefCollection->add($this, $eventDefinition);
    }
}
