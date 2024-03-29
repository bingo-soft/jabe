<?php

namespace Jabe\Impl\Cmd;

use Jabe\Filter\FilterInterface;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class SaveFilterCmd implements CommandInterface
{
    protected $filter;

    public function __construct(?FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function __serialize(): array
    {
        return [
            'filter' => serialize($this->filter)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->filter = unserialize($data['filter']);
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("filter", "filter", $this->filter);

        $operation = $this->filter->getId() === null ? UserOperationLogEntryInterface::OPERATION_TYPE_CREATE : UserOperationLogEntryInterface::PERATION_TYPE_UPDATE;

        $savedFilter = $commandContext
            ->getFilterManager()
            ->insertOrUpdateFilter($this->filter);

        $commandContext->getOperationLogManager()->logFilterOperation($operation, $this->filter->getId());

        return $savedFilter;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
