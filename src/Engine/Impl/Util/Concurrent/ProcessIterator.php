<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class ProcessIterator extends \ArrayIterator
{
    private $remaining; // Number of elements yet to be returned
    public $nextIndex; // Index of element to be returned by next
    private $nextItem;  // Element to be returned by next call to next
    private $lastItem;  // Element returned by last call to next
    public $lastRet;   // Index of last element returned, or -1 if none

    private $collection;

    public function __construct(ProcessQueue $queue)
    {
        $this->collection = $queue;
        $this->lastRet = -1;
        $this->remaining = $queue->count;
        if ($this->remaining > 0) {
            $this->nextIndex = $queue->takeIndex;
            $this->nextItem = $queue->itemAt($this->nextIndex);
        }
    }

    public function valid(): bool
    {
        return $this->remaining > 0;
    }

    public function current()
    {
        return $this->collection->itemAt($this->nextIndex);
    }

    public function next(): void
    {
        if ($this->remaining <= 0) {
            throw new \Exception("no such element");
        }
        $this->lastRet = $this->nextIndex;
        while (
            ($this->remaining -= 1) > 0 && // skip over nulls
            ($this->nextItem = $this->collection->itemAt($this->nextIndex = $this->collection->inc($this->nextIndex))) == null
        ) {
        }
    }
}
