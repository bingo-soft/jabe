<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Bpmndi;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnLabelStyleInterface
};
use Jabe\Model\Bpmn\Instance\Dc\FontInterface;
use Jabe\Model\Bpmn\Instance\Di\StyleInterface;
use Jabe\Model\Bpmn\Impl\Instance\Di\StyleImpl;

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
