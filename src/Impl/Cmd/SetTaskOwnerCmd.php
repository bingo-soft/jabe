<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Task\IdentityLinkType;

class SetTaskOwnerCmd extends AddIdentityLinkCmd
{
    public function __construct(?string $taskId, ?string $userId)
    {
        parent::__construct($taskId, $userId, null, IdentityLinkType::OWNER);
    }

    public function execute(CommandContext $commandContext)
    {
        parent::execute($commandContext);
        $this->task->logUserOperation(UserOperationLogEntryInterface::OPERATION_TYPE_SET_OWNER);
        return null;
    }
}
