<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\IntermediateCatchEventBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    IntermediateCatchEventInterface,
    CatchEventInterface
};

class IntermediateCatchEventImpl extends CatchEventImpl implements IntermediateCatchEventInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IntermediateCatchEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_INTERMEDIATE_CATCH_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(CatchEventInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new IntermediateCatchEventImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function builder(): IntermediateCatchEventBuilder
    {
        return new IntermediateCatchEventBuilder($this->modelInstance, $this);
    }
}
