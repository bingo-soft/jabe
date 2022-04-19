<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Task\IdentityLinkType;

class AssignTaskCmd extends AddIdentityLinkCmd
{
    public function __construct(string $taskId, string $userId)
    {
        parent::__construct($taskId, $userId, null, IdentityLinkType::ASSIGNEE);
    }

    public function execute(CommandContext $commandContext)
    {
        parent::execute($commandContext);
        $this->task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_ASSIGN);
        return null;
    }
}
