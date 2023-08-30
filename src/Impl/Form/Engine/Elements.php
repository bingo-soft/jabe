<?php

namespace Jabe\Impl\Form\Engine;

class Elements
{
    private $elements = [];

    public function push($element): void
    {
        array_unshift($this->elements, $element);
    }

    public function pop()
    {
        return array_shift($this->elements);
    }

    public function size(): int
    {
        return count($this->elements);
    }
}
