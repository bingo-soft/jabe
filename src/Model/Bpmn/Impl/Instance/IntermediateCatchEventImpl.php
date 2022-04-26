<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Bpmn\Builder\IntermediateCatchEventBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            new class implements ModelTypeInstanceProviderInterface
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
