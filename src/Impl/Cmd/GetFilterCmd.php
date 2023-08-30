<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetFilterCmd implements CommandInterface
{
    protected $filterId;

    public function __construct(?string $filterId)
    {
        $this->filterId = $filterId;
    }

    public function __serialize(): array
    {
        return [
            'filterId' => $this->filterId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->filterId = $data['filterId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getFilterManager()
            ->findFilterById($this->filterId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
