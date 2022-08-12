<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Persistence\Entity\ExternalTaskEntity;

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
