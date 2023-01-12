<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteFilterCmd implements CommandInterface, \Serializable
{
    protected $filterId;

    public function __construct(?string $filterId)
    {
        $this->filterId = $filterId;
    }

    public function serialize()
    {
        return json_encode([
            'filterId' => $this->filterId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->filterId = $json->filterId;
    }

    public function execute(CommandContext $commandContext)
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
