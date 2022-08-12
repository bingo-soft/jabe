<?php

namespace Jabe\Impl\Form\Type;

use Jabe\ProcessEngineException;
use Jabe\Variable\Variables;
use Jabe\Variable\Value\TypedValueInterface;

class EnumFormType extends SimpleFormFieldType
{
    public const TYPE_NAME = "enum";

    protected $values = [];

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function getInformation(string $key)
    {
        if ($key == "values") {
            return $this->values;
        }
        return null;
    }

    public function convertValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        $value = $propertyValue->getValue();
        if ($value === null || is_string($value)) {
            $this->validateValue($value);
            return Variables::stringValue(strval($value), $propertyValue->isTransient());
        } else {
            throw new ProcessEngineException("Value '" . $value . "' is not of type String.");
        }
    }

    protected function validateValue($value = null): void
    {
        if ($value !== null) {
            if (!empty($this->values) && !array_key_exists($value, $this->values)) {
                throw new ProcessEngineException("Invalid value for enum form property: " . $value);
            }
        }
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
