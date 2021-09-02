<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Bpmndi;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnDiagramInterface,
    BpmnLabelStyleInterface,
    BpmnPlaneInterface
};
use BpmPlatform\Model\Bpmn\Instance\Di\DiagramInterface;
use BpmPlatform\Model\Bpmn\Impl\Instance\Di\DiagramImpl;

class BpmnDiagramImpl extends DiagramImpl implements BpmnDiagramInterface
{
    protected static $bpmnPlaneChild;
    protected static $bpmnLabelStyleCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BpmnDiagramInterface::class,
            BpmnModelConstants::BPMNDI_ELEMENT_BPMN_DIAGRAM
        )
        ->namespaceUri(BpmnModelConstants::BPMNDI_NS)
        ->extendsType(DiagramInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BpmnDiagramImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$bpmnPlaneChild = $sequenceBuilder->element(BpmnPlaneInterace::class)
        ->required()
        ->build();

        self::$bpmnLabelStyleCollection = $sequenceBuilder->elementCollection(BpmnLabelStyleIntreface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getBpmnPlane(): BpmnPlaneInterface
    {
        return self::$bpmnPlaneChild->getChild($this);
    }

    public function setBpmnPlane(BpmnPlaneInterface $bpmnPlane): void
    {
        self::$bpmnPlaneChild->setChild($this, $bpmnPlane);
    }

    public function getBpmnLabelStyles(): array
    {
        return self::$bpmnLabelStyleCollection->get($this);
    }
}
