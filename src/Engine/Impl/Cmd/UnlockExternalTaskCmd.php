<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExternalTaskEntity;

class UnlockExternalTaskCmd extends ExternalTaskCmd
{
    public function __construct(string $externalTaskId)
    {
        parent::__construct($externalTaskId);
    }

    protected function validateInput(): void
    {
    }

    protected function execute(ExternalTaskEntity $externalTask)
    {
        $externalTask->unlock();
    }

    protected function getUserOperationLogOperationType(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_UNLOCK;
    }
}
