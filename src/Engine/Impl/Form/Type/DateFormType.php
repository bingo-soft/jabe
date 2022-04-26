<?php

namespace Jabe\Engine\Impl\Form\Type;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Type\ValueTypeInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class DateFormType extends AbstractFormFieldType
{
    public const TYPE_NAME = "date";

    protected $datePattern;
    protected $dateFormat;

    public function __construct(string $datePattern)
    {
        $this->datePattern = $datePattern;
        $this->dateFormat = new SimpleDateFormat($datePattern);
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function getInformation(string $key)
    {
        if ("datePattern" == $key) {
            return $datePattern;
        }
        return null;
    }

    public function convertToModelValue(TypedValueInterface $propertyValue): TypedValueInterface
    {
        $value = $propertyValue->getValue();
        if ($value == null) {
            return Variables::dateValue(null, $propertyValue->isTransient());
        } elseif ($value instanceof \DateTime) {
            return Variables::dateValue($value, $propertyValue->isTransient());
        } elseif (is_string($value)) {
            $strValue = trim($value);
            if (empty($strValue)) {
                return Variables::dateValue(null, $propertyValue->isTransient());
            }
            try {
                return Variables::dateValue(new \DateTime($strValue), $propertyValue->isTransient());
            } catch (\Exception $e) {
                throw new ProcessEngineException("Could not parse value '" . $value . "' as date using date format '" . $this->datePattern . "'.");
            }
        } else {
            throw new ProcessEngineException("Value '" . $value . "' cannot be transformed into a Date.");
        }
    }

    public function convertToFormValue(TypedValueInterface $modelValue): TypedValueInterface
    {
        if ($modelValue->getValue() == null) {
            return Variables::stringValue("", $modelValue->isTransient());
        } elseif ($modelValue->getType() == ValueTypeInterface::DATE) {
            return Variables::stringValue($modelValue->getValue(), $modelValue->isTransient());
        } else {
            throw new ProcessEngineException("Expected value to be of type '" . ValueTypeInterface::DATE . "' but got '" . $modelValue->getType() . "'.");
        }
    }
}
