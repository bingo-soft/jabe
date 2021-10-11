<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Bpmndi;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\BaseElementInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnShapeInterface,
    BpmnLabelInterface,
    ParticipantBandKind
};
use BpmPlatform\Model\Bpmn\Instance\Dc\FontInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\LabeledShapeInterface;
use BpmPlatform\Model\Bpmn\Impl\Instance\Di\LabeledShapeImpl;

class BpmnShapeImpl extends LabeledShapeImpl implements BpmnShapeInterface
{
    protected static $bpmnElementAttribute;
    protected static $isHorizontalAttribute;
    protected static $isExpandedAttribute;
    protected static $isMarkerVisibleAttribute;
    protected static $isMessageVisibleAttribute;
    protected static $participantBandKindAttribute;
    protected static $choreographyActivityShapeAttribute;
    protected static $bpmnLabelChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnShapeInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_SHAPE
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(LabeledShapeInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnShapeImpl($instanceContext);
                }
            }
        );

        self::$bpmnElementAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMNDI_ATTRIBUTE_BPMN_ELEMENT)
        ->qNameAttributeReference(BaseElementInterface::class)
        ->build();

        self::$isHorizontalAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_IS_HORIZONTAL
        )
        ->build();

        self::$isExpandedAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMNDI_ATTRIBUTE_IS_EXPANDED)
        ->build();

        self::$isMarkerVisibleAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_IS_MARKER_VISIBLE
        )
        ->build();

        self::$isMessageVisibleAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_IS_MESSAGE_VISIBLE
        )
        ->build();

        self::$participantBandKindAttribute = $typeBuilder->enumAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_PARTICIPANT_BAND_KIND,
            ParticipantBandKind::class
        )
        ->build();

        self::$choreographyActivityShapeAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMNDI_ATTRIBUTE_CHOREOGRAPHY_ACTIVITY_SHAPE
        )
        ->qNameAttributeReference(BpmnShapeInterface::class)
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

    public function isHorizontal(): bool
    {
        return self::$isHorizontalAttribute->getValue($this);
    }

    public function setHorizontal(bool $isHorizontal): void
    {
        self::$isHorizontalAttribute->setValue($this, $isHorizontal);
    }

    public function isExpanded(): bool
    {
        return self::$isExpandedAttribute->getValue($this);
    }

    public function setExpanded(bool $isExpanded): void
    {
        self::$isExpandedAttribute->setValue($this, $isExpanded);
    }

    public function isMarkerVisible(): bool
    {
        return self::$isMarkerVisibleAttribute->getValue($this);
    }

    public function setMarkerVisible(bool $isMarkerVisible): void
    {
        self::$isMarkerVisibleAttribute->setValue($this, $isMarkerVisible);
    }

    public function isMessageVisible(): bool
    {
        return self::$isMessageVisibleAttribute->getValue($this);
    }

    public function setMessageVisible(bool $isMessageVisible): void
    {
        self::$isMessageVisibleAttribute->setValue($this, $isMessageVisible);
    }

    public function getParticipantBandKind(): ?string
    {
        return self::$participantBandKindAttribute->getValue($this);
    }

    public function setParticipantBandKind(string $participantBandKind): void
    {
        self::$participantBandKindAttribute->setValue($this, $participantBandKind);
    }

    public function getChoreographyActivityShape(): ?BpmnShapeInterface
    {
        return self::$choreographyActivityShapeAttribute->getReferenceTargetElement($this);
    }

    public function setChoreographyActivityShape(BpmnShapeInterface $choreographyActivityShape): void
    {
        self::$choreographyActivityShapeAttribute->setReferenceTargetElement($this, $choreographyActivityShape);
    }

    public function getBpmnLabel(): ?BpmnLabelInterface
    {
        return self::$bpmnLabelChild->getChild($this);
    }

    public function setBpmnLabel(BpmnLabelInterface $bpmnLabel): void
    {
        self::$bpmnLabelChild->setChild($this, $bpmnLabel);
    }
}
