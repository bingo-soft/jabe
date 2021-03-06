<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Bpmndi;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelInterface,
    BpmnLabelStyleInterface
};
use Jabe\Model\Bpmn\Instance\Di\LabelInterface;
use Jabe\Model\Bpmn\Impl\Instance\Di\LabelImpl;

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
