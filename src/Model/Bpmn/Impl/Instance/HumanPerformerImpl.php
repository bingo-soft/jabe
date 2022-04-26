<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    HumanPerformerInterface,
    PerformerInterface
};

class HumanPerformerImpl extends PerformerImpl implements HumanPerformerInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            HumanPerformerInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_HUMAN_PERFORMER
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(PerformerInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new HumanPerformerImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
