<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Bpmndi;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\BaseElementInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnPlaneInterface
};
use Jabe\Model\Bpmn\Instance\Dc\FontInterface;
use Jabe\Model\Bpmn\Instance\Di\PlaneInterface;
use Jabe\Model\Bpmn\Impl\Instance\Di\PlaneImpl;

class BpmnPlaneImpl extends PlaneImpl implements BpmnPlaneInterface
{
    protected static $bpmnElementAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnPlaneInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_PLANE
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(PlaneInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnPlaneImpl($instanceContext);
                }
            }
        );

        self::$bpmnElementAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMNDI_ATTRIBUTE_BPMN_ELEMENT)
        ->qNameAttributeReference(BaseElementInterface::class)
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
}
