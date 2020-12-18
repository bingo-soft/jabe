<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface InterfaceInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getImplementationRef(): string;

    public function setImplementationRef(string $implementationRef): void;

    public function getOperations(): array;
}
