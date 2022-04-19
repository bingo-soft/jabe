<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\PropertyChange;

class DeleteGroupIdentityLinkCmd extends DeleteIdentityLinkCmd
{
    public function __construct(string $taskId, string $groupId, string $type)
    {
        parent::__construct($taskId, null, $groupId, $type);
    }

    public function execute(CommandContext $commandContext)
    {
        parent::execute($commandContext);

        $propertyChange = new PropertyChange($this->type, null, $this->groupId);

        $commandContext->getOperationLogManager()
            ->logLinkOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_GROUP_LINK, $this->task, $propertyChange);

        return null;
    }
}
