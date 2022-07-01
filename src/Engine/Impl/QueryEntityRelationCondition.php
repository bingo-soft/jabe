<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Query\QueryPropertyInterface;

class QueryEntityRelationCondition
{
    protected $property;
    protected $comparisonProperty;
    protected $scalarValue;

    public function __construct(QueryPropertyInterface $queryProperty, $propOrValue, $scalarValue = null)
    {
        $this->property = $queryProperty;
        if ($propOrValue instanceof QueryPropertyInterface) {
            $this->comparisonProperty = $comparisonProperty;
        } else {
            $this->scalarValue = $propOrValue;
        }
        if ($scalarValue != null) {
            $this->scalarValue = $scalarValue;
        }
    }

    public function getProperty(): QueryPropertyInterface
    {
        return $this->property;
    }

    public function getComparisonProperty(): ?QueryPropertyInterface
    {
        return $this->comparisonProperty;
    }

    public function getScalarValue()
    {
        return $this->scalarValue;
    }

    /**
     * This assumes that scalarValue and comparisonProperty are mutually exclusive.
     * Either a condition is expressed is by a scalar value, or with a property of another entity.
     */
    public function isPropertyComparison(): bool
    {
        return $this->comparisonProperty != null;
    }

    public function __toString()
    {
        return "QueryEntityRelationCondition["
            . "property=" . $this->property
            . ", comparisonProperty=" . $this->comparisonProperty
            . ", scalarValue=" . $this->scalarValue
            . "]";
    }
}
