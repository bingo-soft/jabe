<?php

namespace Jabe\Engine\Repository;

class DiagramLayout
{
    private $elements;

    public function __construct(array $elements)
    {
        $this->setElements($elements);
    }

    public function getNode(string $id): ?DiagramNode
    {
        $elements = $this->getElements();
        if (array_key_exists($id, $elements) && (($element = $elements[$id]) instanceof DiagramNode)) {
            return $element;
        }
        return null;
    }

    public function getEdge(string $id): ?DiagramEdge
    {
        $elements = $this->getElements();
        if (array_key_exists($id, $elements) && (($element = $elements[$id]) instanceof DiagramEdge)) {
            return $element;
        }
        return null;
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
