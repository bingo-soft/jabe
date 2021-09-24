<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ConditionInterface,
    ExpressionInterface
};

class ConditionImpl extends ExpressionImpl implements ConditionInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConditionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONDITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ExpressionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConditionImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
