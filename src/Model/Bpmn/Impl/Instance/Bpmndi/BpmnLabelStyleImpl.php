<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Bpmndi;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelStyleInterface
};
use BpmPlatform\Model\Bpmn\Instance\Dc\FontInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\StyleInterface;
use BpmPlatform\Model\Bpmn\Impl\Instance\Di\StyleImpl;

class BpmnLabelStyleImpl extends StyleImpl implements BpmnLabelStyleInterface
{
    protected static $fontChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnLabelStyleInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_LABEL_STYLE
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(StyleInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnLabelStyleImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$fontChild = $sequenceBuilder->element(FontInterface::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getFont(): FontInterface
    {
        return self::$fontChild->getChild($this);
    }

    public function setFont(FontInterface $font): void
    {
        self::$fontChild->setChild($this, $font);
    }
}
