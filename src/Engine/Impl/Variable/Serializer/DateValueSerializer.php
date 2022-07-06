<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\DateValueInterface;

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

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): DateValueInterface
    {
        return Variables::dateValue($valueFields->getTextValue(), $asTransientValue);
    }

    public function writeValue(DateValueInterface $typedValue, ValueFieldsInterface $valueFields): void
    {
        $dateValue = $typedValue->getValue();
        if ($dateValue !== null) {
            $valueFields->setTextValue($dateValue);
        } else {
            $valueFields->setTextValue(null);
        }
    }
}
