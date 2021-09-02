<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    CollaborationInterface,
    ConversationAssociationInterface,
    ConversationLinkInterface,
    ConversationNodeInterface,
    CorrelationKeyInterface,
    MessageFlowInterface,
    MessageFlowAssociationInterface,
    ParticipantInterface,
    ParticipantAssociationInterface,
    RootElementInterface
};

class CollaborationImpl extends RootElementImpl implements CollaborationInterface
{
    protected static $nameAttribute;
    protected static $isClosedAttribute;
    protected static $participantCollection;
    protected static $messageFlowCollection;
    protected static $artifactCollection;
    protected static $conversationNodeCollection;
    protected static $conversationAssociationCollection;
    protected static $participantAssociationCollection;
    protected static $messageFlowAssociationCollection;
    protected static $correlationKeyCollection;
    protected static $conversationLinkCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CollaborationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_COLLABORATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CollaborationImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$isClosedAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IS_CLOSED)
        ->defaultValue(false)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$participantCollection = $sequenceBuilder->elementCollection(ParticipantInterface::class)
        ->build();

        self::$messageFlowCollection = $sequenceBuilder->elementCollection(MessageFlowInterface::class)
        ->build();

        self::$artifactCollection = $sequenceBuilder->elementCollection(ArtifactInterface::class)
        ->build();

        self::$conversationNodeCollection = $sequenceBuilder->elementCollection(ConversationNodeInterface::class)
        ->build();

        self::$conversationAssociationCollection = $sequenceBuilder->elementCollection(
            ConversationAssociationInterface::class
        )
        ->build();

        self::$participantAssociationCollection = $sequenceBuilder->elementCollection(
            ParticipantAssociationInterface::class
        )
        ->build();

        self::$messageFlowAssociationCollection = $sequenceBuilder->elementCollection(
            MessageFlowAssociationInterface::class
        )
        ->build();

        self::$correlationKeyCollection = $sequenceBuilder->elementCollection(CorrelationKeyInterface::class)
        ->build();

        self::$conversationLinkCollection = $sequenceBuilder->elementCollection(ConversationLinkInterface::class)
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

    public function isClosed(): bool
    {
        return self::$isClosedAttribute->getValue($this);
    }

    public function setClosed(bool $isClosed): void
    {
        self::$isClosedAttribute->setValue($this, $isClosed);
    }

    public function getParticipants(): array
    {
        return self::$participantCollection->get($this);
    }

    public function getMessageFlows(): array
    {
        return self::$messageFlowCollection->get($this);
    }

    public function getArtifacts(): array
    {
        return self::$artifactCollection->get($this);
    }

    public function getConversationNodes(): array
    {
        return self::$conversationNodeCollection->get($this);
    }

    public function getConversationAssociations(): array
    {
        return self::$conversationAssociationCollection->get($this);
    }

    public function getParticipantAssociations(): array
    {
        return self::$participantAssociationCollection->get($this);
    }

    public function getMessageFlowAssociations(): array
    {
        return self::$messageFlowAssociationCollection->get($this);
    }

    public function getCorrelationKeys(): array
    {
        return self::$correlationKeyCollection->get($this);
    }

    public function getConversationLinks(): array
    {
        return self::$conversationLinkCollection->get($this);
    }
}
