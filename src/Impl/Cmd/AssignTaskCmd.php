<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Task\IdentityLinkType;

class AssignTaskCmd extends AddIdentityLinkCmd
{
    public function __construct(?string $taskId, ?string $userId)
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
