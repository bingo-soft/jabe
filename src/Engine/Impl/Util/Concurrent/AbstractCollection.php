<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

abstract class AbstractCollection implements CollectionInterface
{
    protected $items = [];

    abstract public function size(): int;

    public function isEmpty(): bool
    {
        return $this->size() == 0;
    }

    abstract public function iterator();

    public function contains($el): bool
    {
        $it = $this->iterator();
        while ($it->valid()) {
            if ($it->current() == $el) {
                return true;
            }
            $it->next();
        }
        return false;
    }

    public function toArray(array &$c = null): array
    {
        $r = [];
        $it = $this->iterator();
        while ($it->valid()) {
            $r[] = $it->current();
            $it->next();
        }
        return $r;
    }

    public function add($el): bool
    {
        throw new \Exception("unsupported operation");
    }

    public function remove($el = null)
    {
        $it = $this->iterator();
        if (method_exists($it, 'remove')) {
            while ($it->valid()) {
                if ($it->current() == $el) {
                    $it->remove();
                    return true;
                }
                $it->next();
            }
        } else {
            foreach ($this->items as $key => $val) {
                if ($val == $el) {
                    unset($this->items[$key]);
                    return true;
                }
            }
        }
        return false;
    }
}
