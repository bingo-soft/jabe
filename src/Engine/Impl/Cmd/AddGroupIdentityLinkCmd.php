<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;

class AddGroupIdentityLinkCmd extends AddIdentityLinkCmd
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
            ->logLinkOperation(UserOperationLogEntryInterface::OPERATION_TYPE_ADD_GROUP_LINK, $this->task, $propertyChange);

        return null;
    }
}
