<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Data;

class SimpleStructureDefinition implements FieldBaseStructureDefinitionInterface
{
    protected $id;

    protected $fieldNames = [];

    protected $fieldTypes = [];

    protected $fieldParameterTypes = [];

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getFieldSize(): int
    {
        return count($this->fieldNames);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setFieldName(string $fieldName, string $type, ?string $parameterType): void
    {
        $this->fieldNames[] = $fieldName;
        $this->fieldTypes[] = $type;
        $this->fieldParameterTypes[] = $parameterType;
    }

    public function getFieldNameAt(int $index): ?string
    {
        if ($index >= 0 && $index < count($this->fieldNames)) {
            return $this->fieldNames[$index];
        }
        return null;
    }

    public function getFieldTypeAt(int $index): ?string
    {
        if ($index >= 0 && $index < count($this->fieldTypes)) {
            return $this->fieldTypes[$index];
        }
        return null;
    }

    public function getFieldParameterTypeAt(int $index): ?string
    {
        if ($index >= 0 && $index < count($this->fieldParameterTypes)) {
            return $this->fieldParameterTypes[$index];
        }
        return null;
    }

    public function createInstance(): StructureInstanceInterface
    {
        return new FieldBaseStructureInstance($this);
    }
}
