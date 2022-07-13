<?php

namespace Jabe\Engine\Impl\Metrics;

class Meter
{
    protected $counter;

    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->counter = new \Swoole\Atomic\Long(0);
    }

    public function mark(): void
    {
        $this->counter->add(1);
    }

    public function markTimes(int $times): void
    {
        $this->counter->add($times);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAndClear(): int
    {
        $prev = $this->counter->get();
        $this->counter->set(0);
        return $prev;
    }

    public function get(bool $clear = false): int
    {
        return $clear ? $this->getAndClear() : $this->counter->get();
    }
}
