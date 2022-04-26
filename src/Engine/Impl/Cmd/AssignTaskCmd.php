<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Task\IdentityLinkType;

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
