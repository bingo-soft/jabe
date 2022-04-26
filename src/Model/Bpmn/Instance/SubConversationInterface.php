<?php

namespace Jabe\Model\Bpmn\Instance;

interface SubConversationInterface extends ConversationNodeInterface
{
    public function getConversationNodes(): array;
}
