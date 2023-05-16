<?php

namespace Jabe\Impl;

use Jabe\Impl\Variable\Serializer\VariableSerializersInterface;
use Jabe\Variable\Value\TypedValueInterface;

abstract class AbstractQueryVariableValueCondition
{
    protected $wrappedQueryValue;

    public function __construct(QueryVariableValue $variableValue)
    {
        $this->wrappedQueryValue = $variableValue;
    }

    abstract public function initializeValue(VariableSerializersInterface $serializers, ?TypedValueInterface $typedValue, ?string $dbType): void;

    abstract public function getDisjunctiveConditions(): array;
}
