<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    TaskEntity,
    TaskManager
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Task\IdentityLinkType;

abstract class AddIdentityLinkCmd implements CommandInterface, \Serializable
{
    protected $userId;

    protected $groupId;

    protected $type;

    protected $taskId;

    protected $task;

    public function __construct(string $taskId, string $userId, string $groupId, string $type)
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

    protected function validateParams(?string $userId, ?string $groupId, string $type, string $taskId): void
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $taskId);
        EnsureUtil::ensureNotNull("type is required when adding a new task identity link", "type", $type);

        // Special treatment for assignee, group cannot be used an userId may be null
        if (IdentityLinkType::ASSIGNEE == $type) {
            if ($groupId != null) {
                throw new ProcessEngineException("Incompatible usage: cannot use ASSIGNEE"
                    . " together with a groupId");
            }
        } else {
            if ($userId == null && $groupId == null) {
                throw new ProcessEngineException("userId and groupId cannot both be null");
            }
        }
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkAddIdentityLink($task, $commandContext);

        if (IdentityLinkType::ASSIGNEE == $this->type) {
            $task->setAssignee($this->userId);
        } elseif (IdentityLinkType::OWNER == $this->type) {
            $task->setOwner($this->userId);
        } else {
            $task->addIdentityLink($this->userId, $this->groupId, $this->type);
        }
        $task->triggerUpdateEvent();

        return null;
    }

    protected function checkAddIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkTaskAssign($task);
        }
    }
}
