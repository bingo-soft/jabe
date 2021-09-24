<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Bpmn\Builder\EndEventBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ThrowEventInterface,
    EndEventInterface
};

class EndEventImpl extends ThrowEventImpl implements EndEventInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EndEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_END_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ThrowEventInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new EndEventImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function builder(): EndEventBuilder
    {
        return new EndEventBuilder($this->modelInstance, $this);
    }
}
