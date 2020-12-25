<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\MultiInstanceFlowCondition;
use BpmPlatform\Model\Bpmn\Builder\MultiInstanceLoopCharacteristicsBuilder;
use BpmPlatform\Model\Bpmn\Instance\{
    ComplexBehaviorDefinitionInterface,
    CompletionConditionInterface,
    DataInputInterface,
    DataOutputInterface,
    EventDefinitionInterface,
    InputDataItemInterface,
    LoopCardinalityInterface,
    LoopCharacteristicsInterface,
    OutputDataItemInterface,
    MultiInstanceLoopCharacteristicsInterface
};

class MultiInstanceLoopCharacteristicsImpl extends LoopCharacteristicsImpl implements MultiInstanceLoopCharacteristicsInterface
{
    protected static $isSequentialAttribute;
    protected static $behaviorAttribute;
    protected static $oneBehaviorEventRefAttribute;
    protected static $noneBehaviorEventRefAttribute;
    protected static $loopCardinalityChild;
    protected static $loopDataInputRefChild;
    protected static $loopDataOutputRefChild;
    protected static $inputDataItemChild;
    protected static $outputDataItemChild;
    protected static $complexBehaviorDefinitionCollection;
    protected static $completionConditionChild;
    protected static $asyncAfter;
    protected static $asyncBefore;
    protected static $exclusive;
    protected static $collection;
    protected static $elementVariable;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MultiInstanceLoopCharacteristicsInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_MULTI_INSTANCE_LOOP_CHARACTERISTICS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(LoopCharacteristicsInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new MultiInstanceLoopCharacteristicsImpl($instanceContext);
                }
            }
        );

        self::$isSequentialAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ELEMENT_IS_SEQUENTIAL)
        ->defaultValue(false)
        ->build();

        self::$behaviorAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ELEMENT_BEHAVIOR,
            MultiInstanceFlowCondition::class
        )
        ->defaultValue(MultiInstanceFlowCondition::ALL)
        ->build();

        self::$oneBehaviorEventRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ELEMENT_ONE_BEHAVIOR_EVENT_REF
        )
        ->qNameAttributeReference(EventDefinitionInterface::class)
        ->build();

        self::$noneBehaviorEventRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ELEMENT_NONE_BEHAVIOR_EVENT_REF
        )
        ->qNameAttributeReference(EventDefinitionInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$loopCardinalityChild = $sequenceBuilder->element(LoopCardinalityInterface::class)
        ->build();

        self::$loopDataInputRefChild = $sequenceBuilder->element(LoopDataInputRef::class)
        ->qNameElementReference(DataInputInterface::class)
        ->build();

        self::$loopDataOutputRefChild = $sequenceBuilder->element(LoopDataOutputRef::class)
        ->qNameElementReference(DataOutputInterface::class)
        ->build();

        self::$outputDataItemChild = $sequenceBuilder->element(OutputDataItemInterface::class)
        ->build();

        self::$inputDataItemChild = $sequenceBuilder->element(InputDataItemInterface::class)
        ->build();

        self::$complexBehaviorDefinitionCollection = $sequenceBuilder->elementCollection(
            ComplexBehaviorDefinitionInterface::class
        )
        ->build();

        self::$completionConditionChild = $sequenceBuilder->element(CompletionConditionInterface::class)
        ->build();

        self::$camundaAsyncAfter = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC_AFTER)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        self::$camundaAsyncBefore = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC_BEFORE)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        self::$camundaExclusive = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_EXCLUSIVE)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(true)
        ->build();

        self::$camundaCollection = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_COLLECTION)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$camundaElementVariable = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_ELEMENT_VARIABLE)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): MultiInstanceLoopCharacteristicsBuilder
    {
        return new MultiInstanceLoopCharacteristicsBuilder($this->modelInstance, $this);
    }

    public function getLoopCardinality(): LoopCardinalityInterface
    {
        return self::$loopCardinalityChild->getChild($this);
    }

    public function setLoopCardinality(LoopCardinalityInterface $loopCardinality): void
    {
        self::$loopCardinalityChild->setChild($this, $loopCardinality);
    }

    public function getLoopDataInputRef(): DataInputInterface
    {
        return self::$loopDataInputRefChild->getReferenceTargetElement($this);
    }

    public function setLoopDataInputRef(DataInputInterface $loopDataInputRef): void
    {
        self::$loopDataInputRefChild->setReferenceTargetElement($this, $loopDataInputRef);
    }

    public function getLoopDataOutputRef(): DataOutputInterface
    {
        return self::$loopDataOutputRefChild->getReferenceTargetElement($this);
    }

    public function setLoopDataOutputRef(DataOutputInterface $loopDataOutputRef): void
    {
        self::$loopDataOutputRefChild->setReferenceTargetElement($this, $loopDataOutputRef);
    }

    public function getInputDataItem(): InputDataItemInterface
    {
        return self::$inputDataItemChild->getChild($this);
    }

    public function setInputDataItem(InputDataItemInterface $inputDataItem): void
    {
        self::$inputDataItemChild->setChild($this, $inputDataItem);
    }

    public function getOutputDataItem(): OutputDataItemInterface
    {
        return self::$outputDataItemChild->getChild($this);
    }

    public function setOutputDataItem(OutputDataItemInterface $outputDataItem): void
    {
        self::$outputDataItemChild->setChild($this, $outputDataItem);
    }

    public function getComplexBehaviorDefinitions(): array
    {
        return self::$complexBehaviorDefinitionCollection->get($this);
    }

    public function getCompletionCondition(): CompletionConditionInterface
    {
        return self::$completionConditionChild->getChild($this);
    }

    public function setCompletionCondition(CompletionConditionInterface $completionCondition): void
    {
        self::$completionConditionChild->setChild($this, $completionCondition);
    }

    public function isSequential(): bool
    {
        return self::$isSequentialAttribute->getValue($this);
    }

    public function setSequential(bool $sequential): void
    {
        self::$isSequentialAttribute->setValue($this, $sequential);
    }

    public function getBehavior(): string
    {
        return self::$behaviorAttribute->getValue($this);
    }

    public function setBehavior(string $behavior): void
    {
        self::$behaviorAttribute->setValue($this, $behavior);
    }

    public function getOneBehaviorEventRef(): EventDefinitionInterface
    {
        return self::$oneBehaviorEventRefAttribute->getReferenceTargetElement($this);
    }

    public function setOneBehaviorEventRef(EventDefinitionInterface $oneBehaviorEventRef): void
    {
        self::$oneBehaviorEventRefAttribute->setReferenceTargetElement($this, $oneBehaviorEventRef);
    }

    public function getNoneBehaviorEventRef(): string
    {
        return self::$noneBehaviorEventRefAttribute->getReferenceTargetElement($this);
    }

    public function setNoneBehaviorEventRef(EventDefinitionInterface $noneBehaviorEventRef): void
    {
        self::$noneBehaviorEventRefAttribute->setReferenceTargetElement($this, $noneBehaviorEventRef);
    }

    public function isAsyncBefore(): bool
    {
        return self::$asyncBefore->getValue($this);
    }

    public function setAsyncBefore(bool $isAsyncBefore): void
    {
        self::$asyncBefore->setValue($this, $isAsyncBefore);
    }

    public function isAsyncAfter(): bool
    {
        return self::$asyncAfter->getValue($this);
    }

    public function setAsyncAfter(bool $isAsyncAfter): void
    {
        self::$asyncAfter->setValue($this, $isAsyncAfter);
    }

    public function isExclusive(): bool
    {
        return self::$exclusive->getValue($this);
    }

    public function setExclusive(bool $exclusive): void
    {
        self::$exclusive->setValue($this, $exclusive);
    }

    public function getCollection(): string
    {
        return self::$collection->getValue($this);
    }

    public function setCollection(string $expression): void
    {
        self::$collection->setValue($this, $expression);
    }

    public function getElementVariable(): string
    {
        return self::$elementVariable->getValue($this);
    }

    public function setElementVariable(string $elementVariable): void
    {
        self::$elementVariable->setValue($this, $elementVariable);
    }
}
