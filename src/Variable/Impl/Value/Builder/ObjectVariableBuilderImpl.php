<?php

namespace Jabe\Variable\Impl\Value\Builder;

use Jabe\Variable\Value\Builder\{
    ObjectValueBuilderInterface,
    TypedValueBuilderInterface
};
use Jabe\Variable\Value\{
    ObjectValueInterface,
    SerializationDataFormatInterface
};
use Jabe\Variable\Impl\Value\ObjectValueImpl;

class ObjectVariableBuilderImpl implements ObjectValueBuilderInterface
{
    protected $variableValue;

    public function __construct($value)
    {
        if ($value instanceof ObjectValueInterface) {
            $this->variableValue = $value;
        } else {
            $this->variableValue = new ObjectValueImpl($value);
        }
    }

    public function create(): ObjectValueInterface
    {
        return $this->variableValue;
    }

    public function serializationDataFormat($dataFormat): ObjectValueBuilderInterface
    {
        if ($dataFormat instanceof SerializationDataFormatInterface) {
            $dataFormat = $dataFormat->getName();
        }
        $this->variableValue->setSerializationDataFormat($dataFormat);
        return $this;
    }

    public function setTransient(bool $isTransient): TypedValueBuilderInterface
    {
        $this->variableValue->setTransient($isTransient);
        return $this;
    }
}
