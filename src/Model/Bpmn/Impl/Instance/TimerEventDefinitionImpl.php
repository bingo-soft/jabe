<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    EventDefinitionInterface,
    TimeCycleInterface,
    TimeDateInterface,
    TimeDurationInterface,
    TimerEventDefinitionInterface
};

class TimerEventDefinitionImpl extends EventDefinitionImpl implements TimerEventDefinitionInterface
{
    protected static $timeDateChild;
    protected static $timeDurationChild;
    protected static $timeCycleChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TimerEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_TIMER_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TimerEventDefinitionImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$timeDateChild = $sequenceBuilder->element(TimeDateInterface::class)
        ->build();

        self::$timeDurationChild = $sequenceBuilder->element(TimeDurationInterface::class)
        ->build();

        self::$timeCycleChild = $sequenceBuilder->element(TimeCycleInterface::class)
        ->build();

        $typeBuilder->build();
    }
}
