<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ErrorInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getErrorCode(): string;

    public function setErrorCode(string $errorCode): void;

    public function getErrorMessage(): ?string;

    public function setErrorMessage(string $errorMessage): void;

    public function getStructure(): ItemDefinitionInterface;

    public function setStructure(ItemDefinitionInterface $structure): void;
}
