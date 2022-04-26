<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FormFieldInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getDatePattern(): string;

    public function setDatePattern(string $datePattern): void;

    public function getDefaultValue(): string;

    public function setDefaultValue(string $defaultValue): void;

    public function getProperties(): PropertiesInterface;

    public function setProperties(PropertiesInterface $properties): void;

    public function getValidation(): ValidationInterface;

    public function setValidation(ValidationInterface $validation): void;

    public function getValues(): array;

    public function addValue(ValueInterface $value): void;
}
