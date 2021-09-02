<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Bpmndi;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface,
    BpmnLabelStyleInterface
};
use BpmPlatform\Model\Bpmn\Instance\Di\LabelInterface;
use BpmPlatform\Model\Bpmn\Impl\Instance\Di\LabelImpl;

class BpmnLabelImpl extends LabelImpl implements BpmnLabelInterface
{
    protected static $labelStyleAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnLabelInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_LABEL
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(LabelInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnLabelImpl($instanceContext);
                }
            }
        );

        self::$labelStyleAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMNDI_ATTRIBUTE_LABEL_STYLE)
        ->qNameAttributeReference(BpmnLabelStyleInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getLabelStyle(): BpmnLabelStyleInterface
    {
        return self::$labelStyleAttribute->getReferenceTargetElement($this);
    }

    public function setLabelStyle(BpmnLabelStyleInterface $labelStyle): void
    {
        self::$labelStyleAttribute->setReferenceTargetElement($this, $labelStyle);
    }
}
