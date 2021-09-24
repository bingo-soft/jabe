<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    CorrelationKeyInterface,
    CorrelationPropertyBindingInterface,
    CorrelationSubscriptionInterface
};

class CorrelationSubscriptionImpl extends BaseElementImpl implements CorrelationSubscriptionInterface
{
    protected static $correlationKeyAttribute;
    protected static $correlationPropertyBindingCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CorrelationSubscriptionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CORRELATION_SUBSCRIPTION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CorrelationSubscriptionImpl($instanceContext);
                }
            }
        );

        self::$correlationKeyAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_CORRELATION_KEY_REF
        )
        ->required()
        ->qNameAttributeReference(CorrelationKeyInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$correlationPropertyBindingCollection = $sequenceBuilder->elementCollection(
            CorrelationPropertyBindingInterface::class
        )
        ->build();

        $typeBuilder->build();
    }

    public function getCorrelationKey(): CorrelationKeyInterface
    {
        return self::$correlationKeyAttribute->getReferenceTargetElement($this);
    }

    public function setCorrelationKey(CorrelationKeyInterface $correlationKey): void
    {
        self::$correlationKeyAttribute->setReferenceTargetElement($this, $correlationKey);
    }

    public function getCorrelationPropertyBindings(): array
    {
        return self::$correlationPropertyBindingCollection->get($this);
    }
}
