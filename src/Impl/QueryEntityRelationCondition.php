<?php

namespace Jabe\Impl;

use Jabe\Query\QueryPropertyInterface;

class QueryEntityRelationCondition implements \Serializable
{
    protected $property;
    protected $comparisonProperty;
    protected $scalarValue;

    public function __construct(QueryPropertyInterface $queryProperty, $propOrValue, $scalarValue = null)
    {
        $this->property = $queryProperty;
        if ($propOrValue instanceof QueryPropertyInterface) {
            $this->comparisonProperty = $propOrValue;
        } else {
            $this->scalarValue = $propOrValue;
        }
        if ($scalarValue !== null) {
            $this->scalarValue = $scalarValue;
        }
    }

    public function serialize()
    {
        return json_encode([
            'property' => serialize($this->property),
            'comparisonProperty' => serialize($this->comparisonProperty),
            'scalarValue' => $this->scalarValue
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->property = unserialize($json->property);
        $this->comparisonProperty = unserialize($json->comparisonProperty);
        $this->scalarValue = $json->scalarValue;
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
        return $this->comparisonProperty !== null;
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
