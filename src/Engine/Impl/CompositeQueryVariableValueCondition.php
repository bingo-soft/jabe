<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Variable\Serializer\VariableSerializersInterface;

class CompositeQueryVariableValueCondition extends AbstractQueryVariableValueCondition
{
    protected $aggregatedValues = [];

    public function __construct(QueryVariableValue $variableValue)
    {
        parent::__construct($variableValue);
    }

    public function initializeValue(VariableSerializersInterface $serializers, string $dbType): void
    {
        $typedValue = $this->wrappedQueryValue->getTypedValue();

        $resolver = Context::getProcessEngineConfiguration()->getValueTypeResolver();
        $concreteTypes = $resolver->getSubTypes($typedValue->getType());

        foreach ($concreteTypes as $type) {
            if ($type->canConvertFromTypedValue($typedValue)) {
                $convertedValue = $type->convertFromTypedValue($typedValue);
                $aggregatedValue = new SingleQueryVariableValueCondition($this->wrappedQueryValue);
                $aggregatedValue->initializeValue($serializers, $convertedValue, $dbType);
                $this->aggregatedValues[] = $aggregatedValue;
            }
        }
    }

    public function getDisjunctiveConditions(): array
    {
        return $this->aggregatedValues;
    }
}
