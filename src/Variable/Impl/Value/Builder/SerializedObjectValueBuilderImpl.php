<?php

namespace Jabe\Variable\Impl\Value\Builder;

use Jabe\Variable\Value\Builder\{
    SerializedObjectValueBuilderInterface,
    TypedValueBuilderInterface
};
use Jabe\Variable\Value\{
    ObjectValueInterface,
    SerializationDataFormatInterface
};
use Jabe\Variable\Impl\Value\ObjectValueImpl;

class SerializedObjectValueBuilderImpl implements SerializedObjectValueBuilderInterface
{
    protected $variableValue;

    public function __construct($value = null)
    {
        if ($value instanceof ObjectValueInterface) {
            $this->variableValue = $value;
        } else {
            $this->variableValue = new ObjectValueImpl(null, null, null, null, false);
        }
    }


    public function serializationDataFormat($dataFormat): SerializedObjectValueBuilderInterface
    {
        if ($dataFormat instanceof SerializationDataFormatInterface) {
            $dataFormat = $dataFormat->getName();
        }
        $this->variableValue->setSerializationDataFormat($dataFormat);
        return $this;
    }

    public function create(): ObjectValueInterface
    {
        return $this->variableValue;
    }

    public function objectTypeName(string $typeName): SerializedObjectValueBuilderInterface
    {
        $this->variableValue->setObjectTypeName($typeName);
        return $this;
    }

    public function serializedValue(string $value): SerializedObjectValueBuilderInterface
    {
        $this->variableValue->setSerializedValue($value);
        return $this;
    }

    public function setTransient(bool $isTransient): TypedValueBuilderInterface
    {
        $this->variableValue->setTransient($isTransient);
        return $this;
    }
}
