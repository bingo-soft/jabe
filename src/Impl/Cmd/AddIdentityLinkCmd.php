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
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Task\IdentityLinkType;

abstract class AddIdentityLinkCmd implements CommandInterface
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
        EnsureUtil::ensureNotNull("taskId", "taskId", $taskId);
        EnsureUtil::ensureNotNull("type is required when adding a new task identity link", "type", $type);

        // Special treatment for assignee, group cannot be used an userId may be null
        if (IdentityLinkType::ASSIGNEE == $type) {
            if ($groupId !== null) {
                throw new ProcessEngineException("Incompatible usage: cannot use ASSIGNEE"
                    . " together with a groupId");
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

        $this->checkAddIdentityLink($this->task, $commandContext);

        if (IdentityLinkType::ASSIGNEE == $this->type) {
            $this->task->setAssignee($this->userId);
        } elseif (IdentityLinkType::OWNER == $this->type) {
            $this->task->setOwner($this->userId);
        } else {
            $this->task->addIdentityLink($this->userId, $this->groupId, $this->type);
        }
        $this->task->triggerUpdateEvent();

        return null;
    }

    protected function checkAddIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
