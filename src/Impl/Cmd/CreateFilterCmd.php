<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateFilterCmd implements CommandInterface
{
    protected $resourceType;

    public function __construct(?string $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getFilterManager()
            ->createNewFilter($this->resourceType);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
