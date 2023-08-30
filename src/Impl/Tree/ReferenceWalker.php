<?php

namespace Jabe\Impl\Tree;

abstract class ReferenceWalker
{
    protected $currentElements = [];

    protected $preVisitor = [];

    protected $postVisitor = [];

    abstract protected function nextElements(): array;

    public function __construct($initialElement)
    {
        if (is_array($initialElement)) {
            $this->currentElements = $initialElement;
        } else {
            $this->currentElements[] = $initialElement;
        }
    }

    public function addPreVisitor(TreeVisitorInterface $collector): ReferenceWalker
    {
        $this->preVisitor[] = $collector;
        return $this;
    }

    public function addPostVisitor(TreeVisitorInterface $collector): ReferenceWalker
    {
        $this->postVisitor[] = $collector;
        return $this;
    }

    public function walkWhile(?WalkConditionInterface $condition = null)
    {
        if ($condition === null) {
            $condition = new NullCondition();
        }
        while (!$condition->isFulfilled($this->getCurrentElement())) {
            foreach ($this->preVisitor as $collector) {
                $collector->visit($this->getCurrentElement());
            }

            array_push($this->currentElements, ...$this->nextElements());

            array_shift($this->currentElements);

            foreach ($this->postVisitor as $collector) {
                $collector->visit($this->getCurrentElement());
            }
        }
        return $this->getCurrentElement();
    }

    public function walkUntil(?WalkConditionInterface $condition = null)
    {
        if ($condition === null) {
            $condition = new NullCondition();
        }
        do {
            foreach ($this->preVisitor as $collector) {
                $collector->visit($this->getCurrentElement());
            }

            array_push($this->currentElements, ...$this->nextElements());

            array_shift($this->currentElements);

            foreach ($this->postVisitor as $collector) {
                $collector->visit($this->getCurrentElement());
            }
        } while (!$condition->isFulfilled($this->getCurrentElement()));
        return $this->getCurrentElement();
    }

    public function getCurrentElement()
    {
        if (empty($this->currentElements)) {
            return null;
        } else {
            return $this->currentElements[array_key_first($this->currentElements)];
        }
    }
}
