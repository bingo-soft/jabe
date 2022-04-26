<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            new class implements ModelTypeInstanceProviderInterface
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

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function getSignal(): SignalInterface
    {
        return self::$signalRefAttribute->getReferenceTargetElement($this);
    }

    public function setSignal(SignalInterface $message): void
    {
        self::$signalRefAttribute->setReferenceTargetElement($this, $message);
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
