<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    IdentityLinkEntity,
    TaskEntity
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Task\IdentityLinkType;

class GetIdentityLinksForTaskCmd implements CommandInterface, \Serializable
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
    }

    public function execute(CommandContext $commandContext)
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
        if ($task->getAssignee() != null) {
            $identityLink = new IdentityLinkEntity();
            $identityLink->setUserId($task->getAssignee());
            $identityLink->setTask($task);
            $identityLink->setType(IdentityLinkType::ASSIGNEE);
            $identityLinks[] = $identityLink;
        }
        if ($task->getOwner() != null) {
            $identityLink = new IdentityLinkEntity();
            $identityLink->setUserId($task->getOwner());
            $identityLink->setTask($task);
            $identityLink->setType(IdentityLinkType::OWNER);
            $identityLinks->add($identityLink);
        }

        return $task->getIdentityLinks();
    }

    protected function checkGetIdentityLink(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTask($task);
        }
    }
}
