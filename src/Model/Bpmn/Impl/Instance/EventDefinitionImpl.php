<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    EventDefinitionInterface,
    RootElementInterface
};

abstract class EventDefinitionImpl extends RootElementImpl implements EventDefinitionInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
