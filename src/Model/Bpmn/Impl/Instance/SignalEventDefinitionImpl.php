<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    EventDefinitionInterface,
    SignalInterface,
    SignalEventDefinitionInterface,
    OperationInterface
};

class SignalEventDefinitionImpl extends EventDefinitionImpl implements SignalEventDefinitionInterface
{
    protected static $signalRefAttribute;
    protected static $asyncAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SignalEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_SIGNAL_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new SignalEventDefinitionImpl($instanceContext);
                }
            }
        );

        self::$signalRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_SIGNAL_REF)
        ->qNameAttributeReference(SignalInterface::class)
        ->build();

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function getSignal(): SignalInterface
    {
        return self::$messageRefAttribute->getReferenceTargetElement($this);
    }

    public function setSignal(SignalInterface $message): void
    {
        self::$messageRefAttribute->setReferenceTargetElement($this, $message);
    }

    public function isAsync(): bool
    {
        return self::$asyncAttribute->getValue($this);
    }

    public function setAsync(bool $async): void
    {
        self::$asyncAttribute->setValue($this, $async);
    }
}
