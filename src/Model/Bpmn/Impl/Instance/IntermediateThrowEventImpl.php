<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Bpmn\Builder\IntermediateThrowEventBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
