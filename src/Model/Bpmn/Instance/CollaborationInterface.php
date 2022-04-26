<?php

namespace Jabe\Model\Bpmn\Instance;

interface CollaborationInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function isClosed(): bool;

    public function setClosed(bool $isClosed): void;

    public function getParticipants(): array;

    public function getMessageFlows(): array;

    public function getArtifacts(): array;

    public function getConversationNodes(): array;

    public function getConversationAssociations(): array;

    public function getParticipantAssociations(): array;

    public function getMessageFlowAssociations(): array;

    public function getCorrelationKeys(): array;

    public function getConversationLinks(): array;
}
