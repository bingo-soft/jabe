<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Variables;
use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\DateValueInterface;

class DateValueSerializer extends PrimitiveValueSerializer
{
    public function __construct()
    {
        parent::__construct(ValueType::getDate());
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): DateValueInterface
    {
        return Variables::dateValue($untypedValue->getValue(), $untypedValue->isTransient());
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $deserializeValue, bool $isTransient = false): DateValueInterface
    {
        return Variables::dateValue($valueFields->getTextValue(), $isTransient);
    }

    public function writeValue($typedValue, ValueFieldsInterface $valueFields): void
    {
        $dateValue = $typedValue->getValue();
        if ($dateValue !== null) {
            $valueFields->setTextValue($dateValue);
        } else {
            $valueFields->setTextValue(null);
        }
    }
}
