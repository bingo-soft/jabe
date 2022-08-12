<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Persistence\Entity\{
    ExternalTaskEntity,
    PropertyChange
};

class SetExternalTaskPriorityCmd extends ExternalTaskCmd
{
    /**
     * The priority that should set on the external task.
     */
    protected $priority;

    public function __construct(string $externalTaskId, int $priority)
    {
        parent::__construct($externalTaskId);
        $this->priority = $priority;
    }

    protected function executeTask(ExternalTaskEntity $externalTask)
    {
        $externalTask->setPriority($this->priority);
    }

    protected function validateInput(): void
    {
    }

    protected function getUserOperationLogOperationType(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SET_PRIORITY;
    }

    protected function getUserOperationLogPropertyChanges(ExternalTaskEntity $externalTask): array
    {
        return [
            new PropertyChange("priority", $externalTask->getPriority(), $this->priority)
        ];
    }
}
