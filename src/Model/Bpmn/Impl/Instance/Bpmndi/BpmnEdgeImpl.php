<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Bpmndi;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\BaseElementInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnEdgeInterface,
    BpmnLabelInterface,
    MessageVisibleKind
};
use BpmPlatform\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    LabeledEdgeInterface
};
use BpmPlatform\Model\Bpmn\Impl\Instance\Di\LabeledEdgeImpl;

class BpmnEdgeImpl extends LabeledEdgeImpl implements BpmnEdgeInterface
{
    protected static $bpmnElementAttribute;
    protected static $sourceElementAttribute;
    protected static $targetElementAttribute;
    protected static $messageVisibleKindAttribute;
    protected static $bpmnLabelChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnEdgeInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_EDGE
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(LabeledEdgeInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnEdgeImpl($instanceContext);
                }
            }
        );

        self::$bpmnElementAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMNDI_ATTRIBUTE_BPMN_ELEMENT)
        ->qNameAttributeReference(BaseElementInterface::class)
        ->build();

        self::$sourceElementAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_SOURCE_ELEMENT
        )
        ->qNameAttributeReference(DiagramElementInterface::class)
        ->build();

        self::$targetElementAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_TARGET_ELEMENT
        )
        ->qNameAttributeReference(DiagramElementInterface::class)
        ->build();

        self::$messageVisibleKindAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_MESSAGE_VISIBLE_KIND,
            MessageVisibleKind::class
        )
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$bpmnLabelChild = $sequenceBuilder->element(BpmnLabelInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getBpmnElement(): BaseElementInterface
    {
        return self::$bpmnElementAttribute->getReferenceTargetElement($this);
    }

    public function setBpmnElement(BaseElementInterface $bpmnElement): void
    {
        self::$bpmnElementAttribute->setReferenceTargetElement($this, $bpmnElement);
    }

    public function getSourceElement(): DiagramElementInterface
    {
        return self::$sourceElementAttribute->getReferenceTargetElement($this);
    }

    public function setSourceElement(DiagramElementInterface $sourceElement): void
    {
        self::$sourceElementAttribute->setReferenceTargetElement($this, $sourceElement);
    }

    public function getTargetElement(): DiagramElementInterface
    {
        return self::$targetElementAttribute->getReferenceTargetElement($this);
    }

    public function setTargetElement(DiagramElementInterface $targetElement): void
    {
        self::$targetElementAttribute->getReferenceTargetElement($this, $targetElement);
    }

    public function getMessageVisibleKind(): string
    {
        return self::$messageVisibleKindAttribute->getValue($this);
    }

    public function setMessageVisibleKind(string $messageVisibleKind): void
    {
        self::$messageVisibleKindAttribute->setValue($this, $messageVisibleKind);
    }

    public function getBpmnLabel(): BpmnLabelInterface
    {
        return self::$bpmnLabelChild->getChild($this);
    }

    public function setBpmnLabel(BpmnLabelInterface $bpmnLabel): void
    {
        self::$bpmnLabelChild->setChild($this, $bpmnLabel);
    }
}
