<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivationConditionInterface,
    ExpressionInterface
};

class ActivationConditionImpl extends ExpressionImpl implements ActivationConditionInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ActivationConditionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ACTIVATION_CONDITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendType(ExpressionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ActivationConditionImpl($instanceContext);
                }
            }
        );
        $typeBuilder->build();
    }
}
