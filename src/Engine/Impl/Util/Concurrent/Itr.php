<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class Itr extends \ArrayIterator
{
    private $remaining; // Number of elements yet to be returned
    public $nextIndex; // Index of element to be returned by next
    private $nextItem;  // Element to be returned by next call to next
    private $lastItem;  // Element returned by last call to next
    public $lastRet;   // Index of last element returned, or -1 if none
    private $collection;

    public function __construct(BlockingQueueInterface $queue)
    {
        $queue->lock->trylock();
        try {
            $this->collection = $queue;
            $this->lastRet = -1;
            $this->remaining = $queue->count;
            if ($this->remaining > 0) {
                $this->nextIndex = $queue->takeIndex;
                $this->nextItem = $queue->itemAt($this->nextIndex);
            }
        } finally {
            $queue->lock->unlock();
        }
    }

    public function valid(): bool
    {
        return $this->remaining > 0;
    }

    public function current()
    {
        $this->collection->lock->trylock();
        try {
            return $this->collection->itemAt($this->nextIndex);
        } finally {
            $this->collection->lock->unlock();
        }
    }

    public function next(): void
    {
        if ($this->remaining <= 0) {
            throw new \Exception("no such element");
        }
        $this->collection->lock->trylock();
        try {
            $this->lastRet = $this->nextIndex;
            while (
                ($this->remaining -= 1) > 0 && // skip over nulls
                ($this->nextItem = $this->collection->itemAt($this->nextIndex = $this->collection->inc($this->nextIndex))) == null
            ) {
            }
        } finally {
            $this->collection->lock->unlock();
        }
    }
}
