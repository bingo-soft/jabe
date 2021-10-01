<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Dc\BoundsInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\{
    ShapeInterface,
    NodeInterface
};

abstract class ShapeImpl extends NodeImpl implements ShapeInterface
{
    protected static $boundsChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ShapeInterface::class,
            BpmnModelConstants::DI_ELEMENT_SHAPE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(NodeInterface::class)
        ->abstractType();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$boundsChild = $sequenceBuilder->element(BoundsInterface::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getBounds(): BoundsInterface
    {
        return self::$boundsChild->getChild($this);
    }

    public function setBounds(BoundsInterface $bounds): void
    {
        self::$boundsChild->setChild($this, $bounds);
    }
}
