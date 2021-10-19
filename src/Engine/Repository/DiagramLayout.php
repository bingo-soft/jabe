<?php

namespace BpmPlatform\Engine\Repository;

class DiagramLayout
{
    private $elements;

    public function __construct(array $elements)
    {
        $this->setElements($elements);
    }

    public function getNode(string $id): ?DiagramNode
    {
        $element = $this->getElements()[$id];
        if ($element instanceof DiagramNode) {
            return $element;
        } else {
            return null;
        }
    }

    public function getEdge(string $id): ?DiagramEdge
    {
        $element = $this->getElements()[$id];
        if ($element instanceof DiagramEdge) {
            return $element;
        } else {
            return null;
        }
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    public function getNodes(): array
    {
        $nodes = [];
        foreach ($this->getElements() as $element) {
            if ($element instanceof DiagramNode) {
                $nodes[] = $element;
            }
        }
        return $nodes;
    }
}
