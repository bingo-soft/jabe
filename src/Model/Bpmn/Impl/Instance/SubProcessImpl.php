<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\SubProcessBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    ArtifactInterface,
    FlowElementInterface,
    LaneSetInterface,
    SubProcessInterface
};

class SubProcessImpl extends ActivityImpl implements SubProcessInterface
{
    protected static $triggeredByEventAttribute;
    protected static $laneSetCollection;
    protected static $flowElementCollection;
    protected static $artifactCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SubProcessInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_SUB_PROCESS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ActivityInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new SubProcessImpl($instanceContext);
                }
            }
        );

        self::$triggeredByEventAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_TRIGGERED_BY_EVENT
        )
        ->defaultValue(false)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$laneSetCollection = $sequenceBuilder->elementCollection(LaneSetInterface::class)
        ->build();

        self::$flowElementCollection = $sequenceBuilder->elementCollection(FlowElementInterface::class)
        ->build();

        self::$artifactCollection = $sequenceBuilder->elementCollection(ArtifactInterface::class)
        ->build();

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): SubProcessBuilder
    {
        return new SubProcessBuilder($this->modelInstance, $this);
    }

    public function triggeredByEvent(): bool
    {
        return self::$triggeredByEventAttribute->getValue($this);
    }

    public function setTriggeredByEvent(bool $triggeredByEvent): void
    {
        self::$triggeredByEventAttribute->setValue($this, $triggeredByEvent);
    }

    public function getLaneSets(): array
    {
        return self::$laneSetCollection->get($this);
    }

    public function getFlowElements(): array
    {
        return self::$flowElementCollection->get($this);
    }

    public function getArtifacts(): array
    {
        return self::$artifactCollection->get($this);
    }
}
