<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUniqueTaskWorkerCountCmd implements CommandInterface
{
    protected $startTime;
    protected $endTime;

    public function __construct(?string $startTime, ?string $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function __serialize(): array
    {
        return [
            'startTime' => $this->startTime,
            'endTime' => $this->endTime
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->startTime = $data['startTime'];
        $this->endTime = $data['endTime'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getMeterLogManager()->findUniqueTaskWorkerCount($this->startTime, $this->endTime);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
