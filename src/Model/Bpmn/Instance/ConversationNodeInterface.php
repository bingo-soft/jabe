<?php

namespace Jabe\Model\Bpmn\Instance;

interface ConversationNodeInterface extends BaseElementInterface, InteractionNodeInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getParticipants(): array;

    public function getMessageFlows(): array;

    public function getCorrelationKeys(): array;
}
