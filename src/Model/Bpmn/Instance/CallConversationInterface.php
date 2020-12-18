<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CallConversationInterface extends ConversationNodeInterface
{
    public function getCalledCollaboration(): GlobalConversationInterface;

    public function setCalledCollaboration(GlobalConversationInterface $calledCollaboration): void;

    public function getParticipantAssociations(): array;
}
