<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FormPropertyInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function isRequired(): bool;

    public function setRequired(bool $isRequired): void;

    public function isReadable(): bool;

    public function setReadable(bool $isReadable): void;

    public function isWriteable(): bool;

    public function setWriteable(bool $isWriteable): void;

    public function getVariable(): string;

    public function setVariable(string $variable): void;

    public function getExpression(): string;

    public function setExpression(string $expression): void;

    public function getDatePattern(): string;

    public function setDatePattern(string $datePattern): void;

    public function getDefault(): string;

    public function setDefault(string $default): void;

    public function getValues(): array;

    public function addValue(ValueInterface $value): void;
}
