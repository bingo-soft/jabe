<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Variable\Serializer\VariableSerializersInterface;

abstract class AbstractQueryVariableValueCondition
{
    protected $wrappedQueryValue;

    public function __construct(QueryVariableValue $variableValue)
    {
        $this->wrappedQueryValue = $variableValue;
    }

    abstract public function initializeValue(VariableSerializersInterface $serializers, string $dbType): void;

    abstract public function getDisjunctiveConditions(): array;
}
