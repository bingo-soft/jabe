<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    AssignmentInterface,
    BaseElementInterface,
    DataAssociationInterface,
    FormalExpressionInterface,
    ItemAwareElementInterface
};
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

class DataAssociationImpl extends BaseElementImpl implements DataAssociationInterface
{
    protected static $sourceRefCollection;
    protected static $targetRefChild;
    protected static $transformationChild;
    protected static $assignmentCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataAssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DATA_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DataAssociationImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$sourceRefCollection = $sequenceBuilder->elementCollection(SourceRef::class)
        ->idElementReferenceCollection(ItemAwareElementInterface::class)
        ->build();

        self::$targetRefChild = $sequenceBuilder->element(TargetRef::class)
        ->required()
        ->idElementReference(ItemAwareElementInterface::class)
        ->build();

        self::$transformationChild = $sequenceBuilder->element(Transformation::class)
        ->build();

        self::$assignmentCollection = $sequenceBuilder->elementCollection(AssignmentInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getSources(): array
    {
        return self::$sourceRefCollection->getReferenceTargetElements($this);
    }

    public function getTarget(): ItemAwareElementInterface
    {
        return self::$targetRefChild->getReferenceTargetElement($this);
    }

    public function setTarget(ItemAwareElementInterface $target): void
    {
        self::$targetRefChild->setReferenceTargetElement($this, $target);
    }

    public function getTransformation(): FormalExpressionInterface
    {
        return self::$transformationChild->getChild($this);
    }

    public function setTransformation(Transformation $transformation): void
    {
        self::$transformationChild->setChild($this, $transformation);
    }

    public function getAssignments(): array
    {
        return self::$assignmentCollection->get($this);
    }

    public function getDiagramElement(): BpmnEdgeInterface
    {
        return parent::getDiagramElement();
    }
}
