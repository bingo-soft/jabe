<?php

namespace Jabe\Engine\Impl\Util\Timer;

class AtomicInteger extends \Threaded
{
    private $value;

    public function __construct(int $value = 0)
    {
        $this->value = $value;
    }

    public function getAndIncrement(): int
    {
        $prev = $this->value;
        $this->value += 1;
        return $prev;
    }
}
