<?php

namespace Jabe\Model\Bpmn\Instance;

interface OutputSetInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getDataOutputRefs(): array;

    public function getOptionalOutputRefs(): array;

    public function getWhileExecutingOutputRefs(): array;

    public function getInputSetRefs(): array;
}
