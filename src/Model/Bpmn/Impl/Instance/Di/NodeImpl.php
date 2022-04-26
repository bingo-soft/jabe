<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\{
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
