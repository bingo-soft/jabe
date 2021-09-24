<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Bpmn\Builder\IntermediateThrowEventBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    IntermediateThrowEventInterface,
    ThrowEventInterface
};

class IntermediateThrowEventImpl extends ThrowEventImpl implements IntermediateThrowEventInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IntermediateThrowEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_INTERMEDIATE_THROW_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ThrowEventInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new IntermediateThrowEventImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function builder(): IntermediateThrowEventBuilder
    {
        return new IntermediateThrowEventBuilder($this->modelInstance, $this);
    }
}
