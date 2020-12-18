<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ConversationAssociationInterface extends BaseElementInterface
{
    public function getInnerConversationNode(): ConversationNodeInterface;

    public function setInnerConversationNode(ConversationNodeInterface $innerConversationNode): void;

    public function getOuterConversationNode(): ConversationNodeInterface;

    public function setOuterConversationNode(ConversationNodeInterface $outerConversationNode): void;
}
