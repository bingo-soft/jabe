<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
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
