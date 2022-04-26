<?php

namespace Jabe\Model\Bpmn\Impl;

use Jabe\Model\Xml\Type\ModelElementTypeInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\QueryInterface;

class QueryImpl implements QueryInterface
{
    private $collection;

    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    public function list(): array
    {
        return $this->collection;
    }

    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @param mixed $elementType
     */
    public function filterByType($elementType): QueryInterface
    {
        if ($elementType instanceof ModelElementTypeInterface) {
            $elementClass = $elementType->getInstanceType();
        } else {
            $elementClass = $elementType;
        }
        $filtered = [];
        foreach ($this->collection as $instance) {
            if (is_a($instance, $elementClass)) {
                $filtered[] = $instance;
            }
        }
        return new QueryImpl($filtered);
    }

    public function singleResult(): ModelElementInstanceInterface
    {
        if (count($this->collection) == 1) {
            return $this->collection[0];
        } else {
            throw new BpmnModelException("Collection expected to have <1> entry but has <" . count($this->collection) . ">");
        }
    }
}
