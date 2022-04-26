<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    LoopCharacteristicsInterface
};

abstract class LoopCharacteristicsImpl extends BaseElementImpl implements LoopCharacteristicsInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LoopCharacteristicsInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_LOOP_CHARACTERISTICS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
