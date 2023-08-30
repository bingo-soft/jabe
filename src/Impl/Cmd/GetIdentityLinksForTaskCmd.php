<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    IdentityLinkEntity,
    TaskEntity
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Task\IdentityLinkType;

class GetIdentityLinksForTaskCmd implements CommandInterface
{
    protected $taskId;

    public function __construct(?string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);

        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->taskId);
        EnsureUtil::ensureNotNull("Cannot find task with id " . $this->taskId, "task", $task);

        $this->checkGetIdentityLink($task, $commandContext);

        $identityLinks = $task->getIdentityLinks();

        // assignee is not part of identity links in the db.
        // so if there is one, we add it here.
        // Note: we cant move this code to the TaskEntity (which would be cleaner),
        // since the task.delete cascased to all associated identityLinks
        // and of course this leads to exception while trying to delete a non-existing identityLink
        if ($task->getAssignee() !== null) {
            $identityLink = new IdentityLinkEntity();
            $identityLink->setUserId($task->getAssignee());
            $identityLink->setTask($task);
            $identityLink->setType(IdentityLinkType::ASSIGNEE);
            $identityLinks[] = $identityLink;
        }
        if ($task->getOwner() !== null) {
            $identityLink = new IdentityLinkEntity();
            $identityLink->setUserId($task->getOwner());
            $identityLink->setTask($task);
            $identityLink->setType(IdentityLinkType::OWNER);
            $identityLinks[] = $identityLink;
        }

        return $identityLinks;
    }

    protected function checkGetIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTask($task);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
