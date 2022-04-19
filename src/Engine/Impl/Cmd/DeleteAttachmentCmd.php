<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    AttachmentEntity,
    PropertyChange,
    TaskEntity
};

class DeleteAttachmentCmd implements CommandInterface, \Serializable
{
    protected $attachmentId;

    public function __construct(string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
    }

    public function serialize()
    {
        return json_encode([
            'attachmentId' => $this->attachmentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->attachmentId = $json->attachmentId;
    }

    public function execute(CommandContext $commandContext)
    {
        $attachment = $commandContext
            ->getDbEntityManager()
            ->selectById(AttachmentEntity::class, $this->attachmentId);

        $commandContext
            ->getDbEntityManager()
            ->delete($attachment);

        if ($attachment->getContentId() != null) {
            $commandContext
            ->getByteArrayManager()
            ->deleteByteArrayById($attachment->getContentId());
        }

        if ($attachment->getTaskId() != null) {
            $task = $commandContext
                ->getTaskManager()
                ->findTaskById($attachment->getTaskId());

            $propertyChange = new PropertyChange("name", null, $attachment->getName());

            $commandContext->getOperationLogManager()
                ->logAttachmentOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_ATTACHMENT, $task, $propertyChange);

            $task->triggerUpdateEvent();
        }

        return null;
    }
}
