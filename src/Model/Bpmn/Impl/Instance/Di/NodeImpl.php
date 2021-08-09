<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    NodeInterface
};

abstract class NodeImpl extends DiagramElementImpl implements NodeInterface
{
    protected static $boundsChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            NodeInterface::class,
            BpmnModelConstants::DI_ELEMENT_NODE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(DiagramElementInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
