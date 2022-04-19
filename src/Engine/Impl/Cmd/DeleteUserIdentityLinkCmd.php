<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\PropertyChange;

class DeleteUserIdentityLinkCmd extends DeleteIdentityLinkCmd
{
    public function __construct(string $taskId, string $userId, string $type)
    {
        parent::__construct($taskId, $userId, null, $type);
    }

    public function execute(CommandContext $commandContext)
    {
        parent::execute($commandContext);

        $propertyChange = new PropertyChange($this->type, null, $this->userId);

        $commandContext->getOperationLogManager()
            ->logLinkOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_USER_LINK, $this->task, $propertyChange);

        return null;
    }
}
