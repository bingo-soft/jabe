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

abstract class DeleteIdentityLinkCmd implements CommandInterface, \Serializable
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

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'userId' => $this->userId,
            'groupId' => $this->groupId,
            'type' => $this->type
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->userId = $json->userId;
        $this->groupId = $json->groupId;
        $this->type = $json->type;
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

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkDeleteIdentityLink($task, $commandContext);

        if (IdentityLinkType::ASSIGNEE == $this->type) {
            $task->setAssignee(null);
        } elseif (IdentityLinkType::OWNER == $this->type) {
            $task->setOwner(null);
        } else {
            $task->deleteIdentityLink($this->userId, $this->groupId, $this->type);
        }
        $task->triggerUpdateEvent();

        return null;
    }

    protected function checkDeleteIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }
}
