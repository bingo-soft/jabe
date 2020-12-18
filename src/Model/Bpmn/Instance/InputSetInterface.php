<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface InputSetInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getDataInputs(): array;

    public function getOptionalInputs(): array;

    public function getWhileExecutingInput(): array;

    public function getOutputSets(): array;
}
