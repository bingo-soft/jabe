<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            new class implements ModelTypeInstanceProviderInterface
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

    public function getTimeDate(): ?TimeDateInterface
    {
        return self::$timeDateChild->getChild($this);
    }

    public function setTimeDate(TimeDateInterface $timeDate): void
    {
        self::$timeDateChild->setChild($this, $timeDate);
    }

    public function getTimeDuration(): ?TimeDurationInterface
    {
        return self::$timeDurationChild->getChild($this);
    }

    public function setTimeDuration(TimeDurationInterface $timeDuration): void
    {
        self::$timeDurationChild->setChild($this, $timeDuration);
    }

    public function getTimeCycle(): ?TimeCycleInterface
    {
        return self::$timeCycleChild->getChild($this);
    }

    public function setTimeCycle(TimeCycleInterface $timeCycle): void
    {
        self::$timeCycleChild->setChild($this, $timeCycle);
    }
}
