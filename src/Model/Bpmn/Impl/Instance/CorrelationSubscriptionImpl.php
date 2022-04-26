<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
