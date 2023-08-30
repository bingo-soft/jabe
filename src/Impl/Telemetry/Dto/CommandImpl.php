<?php

namespace Jabe\Impl\Telemetry\Dto;

use Jabe\Telemetry\CommandInterface;

class CommandImpl implements CommandInterface
{
    protected $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function __toString()
    {
        return json_encode([
            'count' => $this->count
        ]);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
