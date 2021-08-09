<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Di\{
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
