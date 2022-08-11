<?php

namespace Jabe\Engine\Impl\Bpmn\Data;

use Juel\TypeConverterImpl;

class FieldBaseStructureInstance implements StructureInstanceInterface
{
    protected $structureDefinition;

    protected $fieldValues = [];

    public function __construct(FieldBaseStructureDefinitionInterface $structureDefinition)
    {
        $this->structureDefinition = $structureDefinition;
    }

    public function setFieldValue(string $fieldName, $value): void
    {
        $this->fieldValues[$fieldName] = $value;
    }

    public function getFieldSize(): int
    {
        return $this->structureDefinition->getFieldSize();
    }

    public function getFieldNameAt(int $index): ?string
    {
        return $this->structureDefinition->getFieldNameAt($index);
    }

    public function getFieldTypeAt(int $index): ?string
    {
        return $this->structureDefinition->getFieldTypeAt($index);
    }

    public function toArray(): array
    {
        $fieldSize = $this->getFieldSize();
        $arguments = [];
        for ($i = 0; $i < $fieldSize; $i += 1) {
            $argument = $this->getFieldValue($i);
            $arguments[$i] = $argument;
        }
        return $arguments;
    }

    public function getFieldValue($field)
    {
        if (is_string($field)) {
            $fieldName = $field;
            if (array_key_exists($fieldName, $this->fieldValues)) {
                return $this->fieldValues[$fieldName];
            }
            return null;
        } elseif (is_int($field)) {
            $index = $field;
            $fieldName = $this->getFieldNameAt($index);
            $fieldValueObject = $this->getFieldValue($fieldName);
            $converter = new TypeConverterImpl();
            return $converter->convert($fieldValueObject, $this->getFieldTypeAt($index));
        }
    }

    public function loadFrom(array $array): void
    {
        $fieldSize = $this->getFieldSize();
        for ($i = 0; $i < $fieldSize; $i += 1) {
            $fieldName = $this->getFieldNameAt($i);
            $fieldValue = $array[$i];
            $this->setFieldValue($fieldName, $fieldValue);
        }
    }
}
