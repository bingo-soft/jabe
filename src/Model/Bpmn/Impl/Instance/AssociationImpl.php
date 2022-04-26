<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\AssociationDirection;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    ArtifactInterface,
    AssociationInterface,
    BaseElementInterface
};
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

class AssociationImpl extends ArtifactImpl implements AssociationInterface
{
    protected static $sourceRefAttribute;
    protected static $targetRefAttribute;
    protected static $associationDirectionAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            AssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ArtifactInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new AssociationImpl($instanceContext);
                }
            }
        );

        self::$sourceRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_SOURCE_REF)
        ->required()
        ->qNameAttributeReference(BaseElementInterface::class)
        ->build();

        self::$targetRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TARGET_REF)
        ->required()
        ->qNameAttributeReference(BaseElementInterface::class)
        ->build();

        self::$associationDirectionAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ASSOCIATION_DIRECTION,
            AssociationDirection::class
        )
        ->defaultValue(AssociationDirection::NONE)
        ->build();

        $typeBuilder->build();
    }

    public function getSource(): BaseElementInterface
    {
        return self::$sourceRefAttribute->getReferenceTargetElement($this);
    }

    public function setSource(BaseElementInterface $source): void
    {
        self::$sourceRefAttribute->setReferenceTargetElement($this, $source);
    }

    public function getTarget(): BaseElementInterface
    {
        return self::$targetRefAttribute->getReferenceTargetElement($this);
    }

    public function setTarget(BaseElementInterface $target): void
    {
        self::$targetRefAttribute->setReferenceTargetElement($this, $target);
    }

    public function getAssociationDirection(): string
    {
        return self::$associationDirectionAttribute->getValue($this);
    }

    public function setAssociationDirection(string $associationDirection): void
    {
        self::$associationDirectionAttribute->setValue($this, $associationDirection);
    }

    public function getDiagramElement(): BpmnEdgeInterface
    {
        return parent::getDiagramElement();
    }
}
