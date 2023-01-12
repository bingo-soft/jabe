<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\PropertyChange;

class AddUserIdentityLinkCmd extends AddIdentityLinkCmd
{
    public function __construct(?string $taskId, ?string $userId, ?string $type)
    {
        parent::__construct($taskId, $userId, null, $type);
    }

    public function execute(CommandContext $commandContext)
    {
        parent::execute($commandContext);

        $propertyChange = new PropertyChange($this->type, null, $this->userId);

        $commandContext->getOperationLogManager()
            ->logLinkOperation(UserOperationLogEntryInterface::OPERATION_TYPE_ADD_USER_LINK, $this->task, $propertyChange);

        return null;
    }
}
