<?php

namespace Jabe\Engine\Impl\Util\Timer;

class TaskQueue extends \Threaded
{
    /**
     * Priority queue represented as a balanced binary heap: the two children
     * of queue[n] are queue[2*n] and queue[2*n+1].  The priority queue is
     * ordered on the nextExecutionTime field: The TimerTask with the lowest
     * nextExecutionTime is in queue[1] (assuming the queue is nonempty).  For
     * each node n in the heap, and each descendant of n, d,
     * n.nextExecutionTime <= d.nextExecutionTime.
     */
    private $queue = [];

    /**
     * The number of tasks in the priority queue.  (The tasks are stored in
     * queue[1] up to queue[size]).
     */
    private $size = 0;

    /**
     * Returns the number of tasks currently on the queue.
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Adds a new task to the priority queue.
     */
    public function add(TimerTask $task): void
    {
        $this->size += 1;
        $this->queue[$this->size] = $task;
        $this->fixUp($this->size);
    }

    /**
     * Return the "head task" of the priority queue.  (The head task is an
     * task with the lowest nextExecutionTime.)
     */
    public function getMin(): ?TimerTask
    {
        if (count($this->queue) >= 1) {
            return $this->queue[1];
        }
        return null;
    }

    /**
     * Return the ith task in the priority queue, where i ranges from 1 (the
     * head task, which is returned by getMin) to the number of tasks on the
     * queue, inclusive.
     */
    public function get(int $i): ?TimerTask
    {
        if (count($this->queue) > $i) {
            return $this->queue[$i];
        }
        return null;
    }

    /**
     * Remove the head task from the priority queue.
     */
    public function removeMin(): void
    {
        $this->queue[1] = $this->queue[$this->size];
        $this->size -= 1;
        $this->queue[$this->size] = null;  // Drop extra reference to prevent memory leak
        $this->fixDown(1);
    }

    /**
     * Removes the ith element from queue without regard for maintaining
     * the heap invariant.  Recall that queue is one-based, so
     * 1 <= i <= size.
     */
    public function quickRemove(int $i): void
    {
        if ($i <= $this->size) {
            $this->queue[$i] = $this->queue[$this->size];
            $this->size -= 1;
            $this->queue[$this->size] = null;  // Drop extra ref to prevent memory leak
        } else {
            throw new \Exception("quickRemove: $i index is missing in queue");
        }
    }

    /**
     * Sets the nextExecutionTime associated with the head task to the
     * specified value, and adjusts priority queue accordingly.
     */
    public function rescheduleMin(int $newTime): void
    {
        $this->queue[1]->getnextExecutionTime = $newTime;
        $this->fixDown(1);
    }

    /**
     * Returns true if the priority queue contains no elements.
     */
    public function isEmpty(): bool
    {
        return $this->size == 0;
    }

    /**
     * Removes all elements from the priority queue.
     */
    public function clear(): void
    {
        // Null out task references to prevent memory leak
        for ($i = 1; $i <= $this->size; $i += 1) {
            $this->queue[$i] = null;
        }
        $this->size = 0;
    }

    /**
     * Establishes the heap invariant (described above) assuming the heap
     * satisfies the invariant except possibly for the leaf-node indexed by k
     * (which may have a nextExecutionTime less than its parent's).
     *
     * This method functions by "promoting" queue[k] up the hierarchy
     * (by swapping it with its parent) repeatedly until queue[k]'s
     * nextExecutionTime is greater than or equal to that of its parent.
     */
    private function fixUp(int $k): void
    {
        while ($k > 1) {
            $j = $k >> 1;
            if ($this->queue[j]->nextExecutionTime <= $this->queue[$k]->nextExecutionTime) {
                break;
            }
            $tmp = $this->queue[$j];
            $this->queue[$j] = $this->queue[$k];
            $this->queue[$k] = $tmp;
            $k = $j;
        }
    }

    /**
     * Establishes the heap invariant (described above) in the subtree
     * rooted at k, which is assumed to satisfy the heap invariant except
     * possibly for node k itself (which may have a nextExecutionTime greater
     * than its children's).
     *
     * This method functions by "demoting" queue[k] down the hierarchy
     * (by swapping it with its smaller child) repeatedly until queue[k]'s
     * nextExecutionTime is less than or equal to those of its children.
     */
    private function fixDown(int $k): void
    {
        $j = null;
        while (($j = $k << 1) <= $this->size && $j > 0) {
            if (
                $j < $this->size &&
                $this->queue[$j]->nextExecutionTime > $this->queue[$j + 1]->nextExecutionTime
            ) {
                $j += 1; // j indexes smallest kid
            }
            if ($this->queue[$k]->nextExecutionTime <= $this->queue[$j]->nextExecutionTime) {
                break;
            }
            $tmp = $this->queue[$j];
            $this->queue[$j] = $this->queue[$k];
            $this->queue[$k] = $tmp;
            $k = $j;
        }
    }

    /**
     * Establishes the heap invariant (described above) in the entire tree,
     * assuming nothing about the order of the elements prior to the call.
     */
    public function heapify(): void
    {
        for ($i = $this->size / 2; $i >= 1; $i -= 1) {
            $this->fixDown($i);
        }
    }
}
