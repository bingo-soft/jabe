<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    CompensateEventDefinitionInterface,
    EventDefinitionInterface
};

class CompensateEventDefinitionImpl extends EventDefinitionImpl implements CompensateEventDefinitionInterface
{
    protected static $waitForCompletionAttribute;
    protected static $activityRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CompensateEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_COMPENSATE_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CompensateEventDefinitionImpl($instanceContext);
                }
            }
        );

        self::$waitForCompletionAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_WAIT_FOR_COMPLETION
        )
        ->build();

        self::$activityRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ACTIVITY_REF)
        ->qNameAttributeReference(ActivityInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function isWaitForCompletion(): bool
    {
        return self::$waitForCompletionAttribute->getValue($this);
    }

    public function setWaitForCompletion(bool $isWaitForCompletion): void
    {
        self::$waitForCompletionAttribute->setValue($this, $isWaitForCompletion);
    }

    public function getActivity(): ActivityInterface
    {
        return self::$activityRefAttribute->getReferenceTargetElement($this);
    }

    public function setActivity(ActivityInterface $activity): void
    {
        self::$activityRefAttribute->setReferenceTargetElement($this, $activity);
    }
}
