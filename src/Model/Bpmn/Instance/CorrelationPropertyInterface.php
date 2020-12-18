<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface CorrelationPropertyInterface extends RootElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getType(): ItemDefinitionInterface;

    public function setType(ItemDefinitionInterface $type): void;

    public function getCorrelationPropertyRetrievalExpressions(): array;
}
