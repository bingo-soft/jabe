<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Type\Reference\AttributeReferenceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\QueryInterface;
use BpmPlatform\Model\Bpmn\Builder\{
    AbstractBaseElementBuilder,
    AbstractFlowNodeBuilder
};
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\Impl\QueryImpl;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    FlowElementInterface,
    FlowNodeInterface,
    SequenceFlowInterface
};

abstract class FlowNodeImpl extends FlowElementImpl implements FlowNodeInterface
{
    protected static $incomingCollection;
    protected static $outgoingCollection;
    protected static $asyncAfter;
    protected static $asyncBefore;
    protected static $exclusive;
    protected static $jobPriority;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(FlowNodeInterface::class, BpmnModelConstants::BPMN_ELEMENT_FLOW_NODE)
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FlowElementInterface::class)
        ->abstractType();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$incomingCollection = $sequenceBuilder->elementCollection(Incoming::class)
        ->qNameElementReferenceCollection(SequenceFlowInterface::class)
        ->build();

        self::$outgoingCollection = $sequenceBuilder->elementCollection(Outgoing::class)
        ->qNameElementReferenceCollection(SequenceFlowInterface::class)
        ->build();

        self::$asyncAfter = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC_AFTER)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        self::$asyncBefore = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC_BEFORE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        self::$exclusive = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_EXCLUSIVE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(true)
        ->build();


        self::$jobPriority = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_JOB_PRIORITY)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): AbstractBaseElementBuilder//AbstractFlowNodeBuilder
    {
        throw new BpmnModelException("No builder implemented");
    }

    public function addOutgoing(SequenceFlowInterface $node): void
    {
        self::$outgoingCollection->add($this, $node);
    }

    public function addIncoming(SequenceFlowInterface $node): void
    {
        self::$incomingCollection->add($this, $node);
    }

    public function updateAfterReplacement(): void
    {
        parent::updateAfterReplacement();
        $incomingReferences = $this->getIncomingReferencesByType(SequenceFlowInterface::class);
        foreach ($incomingReferences as $reference) {
            foreach ($reference->findReferenceSourceElements($this) as $sourceElement) {
                $referenceIdentifier = $reference->getReferenceIdentifier($sourceElement);
                if (
                    $referenceIdentifier != null &&
                    $referenceIdentifier == $this->getId() &&
                    $reference instanceof AttributeReferenceInterface
                ) {
                    $attributeName = $reference->getReferenceSourceAttribute()->getAttributeName();
                    if ($attributeName == BpmnModelConstants::BPMN_ATTRIBUTE_SOURCE_REF) {
                        self::$outgoingCollection->add($this, $sourceElement);
                    } elseif ($attributeName == BpmnModelConstants::BPMN_ATTRIBUTE_TARGET_REF) {
                        self::$incomingCollection->add($this, $sourceElement);
                    }
                }
            }
        }
    }

    public function getIncoming(): array
    {
        return self::$incomingCollection->getReferenceTargetElements($this);
    }

    public function getOutgoing(): array
    {
        return self::$outgoingCollection->getReferenceTargetElements($this);
    }

    public function getPreviousNodes(): QueryInterface
    {
        $previousNodes = [];
        foreach ($this->getInoming() as $sequenceFlow) {
            $previousNodes[] = $sequenceFlow->getSource();
        }
        return new QueryImpl($previousNodes);
    }

    public function getSucceedingNodes(): QueryInterface
    {
        $succeedingNodes = [];
        foreach ($this->getOutgoing() as $sequenceFlow) {
            $succeedingNodes[] = $sequenceFlow->getTarget();
        }
        return new QueryImpl($succeedingNodes);
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

    public function setExclusive(bool $isExclusive): void
    {
        self::$exclusive->setValue($this, $isExclusive);
    }

    public function getJobPriority(): ?string
    {
        return self::$jobPriority->getValue($this);
    }

    public function setJobPriority(string $jobPriority): void
    {
        self::$jobPriority->setValue($this, $jobPriority);
    }
}
