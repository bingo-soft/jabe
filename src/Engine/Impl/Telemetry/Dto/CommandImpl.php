<?php

namespace Jabe\Engine\Impl\Telemetry\Dto;

use Jabe\Engine\Telemetry\CommandInterface;

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
}
