<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\AttachmentEntity;
use BpmPlatform\Engine\Task\AttachmentInterface;

class SaveAttachmentCmd implements CommandInterface
{
    protected $attachment;

    public function __construct(AttachmentInterface $attachment)
    {
        $this->attachment = $attachment;
    }

    public function execute(CommandContext $commandContext)
    {
        $updateAttachment = $commandContext
            ->getDbEntityManager()
            ->selectById(AttachmentEntity::class, $this->attachment->getId());

        $updateAttachment->setName($this->attachment->getName());
        $updateAttachment->setDescription($this->attachment->getDescription());

        $taskId = $this->attachment->getTaskId();
        if ($taskId != null) {
            $task = $commandContext->getTaskManager()->findTaskById($taskId);

            if ($task != null) {
                $task->triggerUpdateEvent();
            }
        }

        return null;
    }
}
