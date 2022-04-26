<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Dc\BoundsInterface;
use Jabe\Model\Bpmn\Instance\Di\{
    LabelInterface,
    NodeInterface
};

abstract class LabelImpl extends NodeImpl implements LabelInterface
{
    protected static $boundsChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LabelInterface::class,
            BpmnModelConstants::DI_ELEMENT_LABEL
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(NodeInterface::class)
        ->abstractType();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$boundsChild = $sequenceBuilder->element(BoundsInterface::class)
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
