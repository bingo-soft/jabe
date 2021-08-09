<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Di\{
    LabeledShapeInterface,
    ShapeInterface
};

abstract class LabeledShapeImpl extends ShapeImpl implements LabeledShapeInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LabeledShapeInterface::class,
            BpmnModelConstants::DI_ELEMENT_LABELED_SHAPE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(ShapeInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
