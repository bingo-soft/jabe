<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\{
    LabeledEdgeInterface,
    EdgeInterface
};

abstract class LabeledEdgeImpl extends EdgeImpl implements LabeledEdgeInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LabeledEdgeInterface::class,
            BpmnModelConstants::DI_ELEMENT_LABELED_EDGE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(EdgeInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
