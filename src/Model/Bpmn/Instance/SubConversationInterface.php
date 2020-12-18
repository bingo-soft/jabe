<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface SubConversationInterface extends ConversationNodeInterface
{
    public function getConversationNodes(): array;
}
