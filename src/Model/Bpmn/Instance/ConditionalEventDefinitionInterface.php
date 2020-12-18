<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ConditionalEventDefinitionInterface extends EventDefinitionInterface
{
    public function getCondition(): ConditionInterface;

    public function setCondition(ConditionInterface $condition): void;

    public function getVariableName(): string;

    public function setVariableName(string $variableName): void;

    public function getVariableEvents(): string;

    public function setVariableEvents(string $variableEvents): void;

    public function getVariableEventsList(): array;

    public function setVariableEventsList(array $variableEventsList): void;
}
