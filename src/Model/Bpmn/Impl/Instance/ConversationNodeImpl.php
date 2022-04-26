<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    ConversationNodeInterface,
    CorrelationKeyInterface,
    MessageFlowInterface,
    ParticipantInterface
};

class ConversationNodeImpl extends BaseElementImpl implements ConversationNodeInterface
{
    protected static $nameAttribute;
    protected static $participantRefCollection;
    protected static $messageFlowRefCollection;
    protected static $correlationKeyCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConversationNodeInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONVERSATION_NODE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->abstractType();

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$participantRefCollection = $sequenceBuilder->elementCollection(ParticipantRef::class)
        ->qNameElementReferenceCollection(ParticipantInterface::class)
        ->build();

        self::$messageFlowRefCollection = $sequenceBuilder->elementCollection(MessageFlowRef::class)
        ->qNameElementReferenceCollection(MessageFlowInterface::class)
        ->build();

        self::$correlationKeyCollection = $sequenceBuilder->elementCollection(CorrelationKeyInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getParticipants(): array
    {
        return self::$participantRefCollection->getReferenceTargetElements($this);
    }

    public function getMessageFlows(): array
    {
        return self::$messageFlowRefCollection->getReferenceTargetElements($this);
    }

    public function getCorrelationKeys(): array
    {
        return self::$correlationKeyCollection->get($this);
    }
}
