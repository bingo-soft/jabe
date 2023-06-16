<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteFilterCmd implements CommandInterface
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
        $commandContext->getOperationLogManager()->logFilterOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE, $this->filterId);

        $commandContext
            ->getFilterManager()
            ->deleteFilter($this->filterId);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
