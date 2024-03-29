<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    TaskEntity,
    TaskManager
};
use Jabe\Task\IdentityLinkType;
use Jabe\Impl\Util\EnsureUtil;

abstract class DeleteIdentityLinkCmd implements CommandInterface
{
    protected $userId;

    protected $groupId;

    protected $type;

    protected $taskId;

    protected $task;

    public function __construct(?string $taskId, ?string $userId, ?string $groupId, ?string $type)
    {
        $this->validateParams($userId, $groupId, $type, $taskId);
        $this->taskId = $taskId;
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->type = $type;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId,
            'userId' => $this->userId,
            'groupId' => $this->groupId,
            'type' => $this->type
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->userId = $data['userId'];
        $this->groupId = $data['groupId'];
        $this->type = $data['type'];
    }

    protected function validateParams(?string $userId, ?string $groupId, ?string $type, ?string $taskId): void
    {
        EnsureUtil::ensureNotNull("taskId", "taskIds", $taskId);
        EnsureUtil::ensureNotNull("type is required when adding a new task identity link", "type", $type);

        // Special treatment for assignee and owner: group cannot be used and userId may be null
        if (IdentityLinkType::ASSIGNEE == $type || IdentityLinkType::OWNER == $type) {
            if ($groupId !== null) {
                throw new ProcessEngineException("Incompatible usage: cannot use type '" . $type
                    . "' together with a groupId");
            }
        } else {
            if ($userId === null && $groupId === null) {
                throw new ProcessEngineException("userId and groupId cannot both be null");
            }
        }
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $this->task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $this->task);

        $this->checkDeleteIdentityLink($this->task, $commandContext);

        if (IdentityLinkType::ASSIGNEE == $this->type) {
            $this->task->setAssignee(null);
        } elseif (IdentityLinkType::OWNER == $this->type) {
            $this->task->setOwner(null);
        } else {
            $this->task->deleteIdentityLink($this->userId, $this->groupId, $this->type);
        }
        $this->task->triggerUpdateEvent();

        return null;
    }

    protected function checkDeleteIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }
}
