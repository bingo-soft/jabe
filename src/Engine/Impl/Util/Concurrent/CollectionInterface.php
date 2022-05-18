<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

interface CollectionInterface
{
    public function size(): int;

    public function isEmpty(): bool;

    public function iterator();

    public function contains($el): bool;

    public function toArray(array &$c = null): array;

    public function add($el): bool;

    public function remove($el = null);
}
