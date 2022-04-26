<?php

namespace Jabe\Model\Bpmn\Instance;

interface ConversationLinkInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getSource(): InteractionNodeInterface;

    public function setSource(InteractionNodeInterface $source): void;

    public function getTarget(): InteractionNodeInterface;

    public function setTarget(InteractionNodeInterface $target): void;
}
