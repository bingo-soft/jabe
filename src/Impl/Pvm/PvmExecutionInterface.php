<?php

namespace Jabe\Impl\Pvm;

interface PvmExecutionInterface
{
    public function signal(string $signalName, $signalData): void;

    public function getActivity(): ?PvmActivityInterface;

    public function hasVariable(string $variableName): bool;
    public function setVariable(string $variableName, $value): void;
    public function getVariable(string $variableName);
    public function getVariables(): array;
}
