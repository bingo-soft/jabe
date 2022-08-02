<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Variable\Serializer\VariableSerializersInterface;
use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class QueryVariableValue implements \Serializable
{
    protected $name;
    protected $value;
    protected $operator;
    protected $local;
    protected $valueCondition;
    protected $variableNameIgnoreCase;
    protected $variableValueIgnoreCase;

    public function __construct(string $name, $value, string $operator, bool $local, ?bool $variableNameIgnoreCase = false, ?bool $variableValueIgnoreCase = false)
    {
        $this->name = $name;
        $this->value = Variables::untypedValue($value);
        $this->operator = $operator;
        $this->local = $local;
        $this->variableNameIgnoreCase = $variableNameIgnoreCase;
        $this->variableValueIgnoreCase = $variableValueIgnoreCase;
    }

    public function initialize(VariableSerializersInterface $serializers, string $dbType): void
    {
        if ($this->value->getType() !== null && $this->value->getType()->isAbstract()) {
            $this->valueCondition = new CompositeQueryVariableValueCondition($this);
        } else {
            $this->valueCondition = new SingleQueryVariableValueCondition($this);
        }

        $this->valueCondition->initializeValue($serializers, $dbType);
    }

    public function getValueConditions(): array
    {
        return $this->valueCondition->getDisjunctiveConditions();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperator(): string
    {
        if ($this->operator !== null) {
            return $this->operator;
        }
        return QueryOperator::EQUALS;
    }

    public function getOperatorName(): string
    {
        return $this->getOperator();
    }

    public function getValue()
    {
        return $this->value->getValue();
    }

    public function getTypedValue(): TypedValueInterface
    {
        return $this->value;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function isVariableNameIgnoreCase(): bool
    {
        return $this->variableNameIgnoreCase;
    }

    public function setVariableNameIgnoreCase(bool $variableNameIgnoreCase): void
    {
        $this->variableNameIgnoreCase = $variableNameIgnoreCase;
    }

    public function isVariableValueIgnoreCase(): bool
    {
        return $this->variableValueIgnoreCase;
    }

    public function setVariableValueIgnoreCase(bool $variableValueIgnoreCase): void
    {
        $this->variableValueIgnoreCase = $variableValueIgnoreCase;
    }
}
