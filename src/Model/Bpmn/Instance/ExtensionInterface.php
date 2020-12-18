<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ExtensionInterface extends BpmnModelElementInstanceInterface
{
    public function getDefinition(): string;

    public function setDefinition(string $definition): void;

    public function mustUnderstand(): bool;

    public function setMustUnderstand(bool $mustUnderstand): void;

    public function getDocumentations(): array;
}
