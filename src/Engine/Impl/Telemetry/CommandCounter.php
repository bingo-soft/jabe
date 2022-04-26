<?php

namespace Jabe\Engine\Impl\Telemetry;

class CommandCounter
{
    protected $name;
    protected $count = 0;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function mark(int $times = 0): void
    {
        $this->count += $times;
    }

    public function getAndClear(): int
    {
        $prev = $this->count;
        $this->count = 0;
        return $prev;
    }

    public function get(): int
    {
        return $this->count;
    }
}
