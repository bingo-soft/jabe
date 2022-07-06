<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

abstract class AbstractQueue extends AbstractCollection
{
    public function add($e): bool
    {
        if ($this->offer($e, null)) {
            return true;
        } else {
            throw new \Exception("Queue full");
        }
    }

    public function remove($el = null)
    {
        $x = $this->poll();
        if ($x !== null) {
            return $x;
        } else {
            throw new \Exception("No such element");
        }
    }

    public function element()
    {
        $x = $this->peek();
        if ($x !== null) {
            return $x;
        } else {
            throw new \Exception("No such element");
        }
    }

    public function clear(): void
    {
        while ($this->poll() !== null) {
        }
    }

    public function addAll($c = null): bool
    {
        if ($c === null) {
            throw new \Exception("nothing to add");
        }
        if ($c == $this) {
            throw new \Exception("Illegal argument");
        }
        $modified = false;
        if (is_array($c)) {
            foreach ($c as $e) {
                if ($this->add($e)) {
                    $modified = true;
                }
            }
        }
        return $modified;
    }
}
