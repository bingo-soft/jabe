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
    PotentialOwnerInterface
};

class PotentialOwnerImpl extends HumanPerformerImpl implements PotentialOwnerInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            PotentialOwnerInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_POTENTIAL_OWNER
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(HumanPerformerInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new PotentialOwnerImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
