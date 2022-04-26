<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\StringValueInterface;

class StringValueSerializer extends PrimitiveValueSerializer
{
    public const EMPTY_STRING = "!emptyString!";

    public function __construct()
    {
        parent::__construct(ValueType::getString());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): StringValueInterface
    {
        return Variables::stringValue($untypedValue->getValue(), $untypedValue->isTransient());
    }

    public function writeValue(StringValueInterface $variableValue, ValueFieldsInterface $valueFields): void
    {
        $value = $variableValue->getValue();
        $valueFields->setTextValue($value);
        if ($value == "") {
            $valueFields->setTextValue2(self::EMPTY_STRING);
        }
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): StringValueInterface
    {
        $textValue = $valueFields->getTextValue();
        if ($textValue == null && self::EMPTY_STRING == $valueFields->getTextValue2()) {
            $textValue = "";
        }
        return Variables::stringValue($textValue, $asTransientValue);
    }
}
