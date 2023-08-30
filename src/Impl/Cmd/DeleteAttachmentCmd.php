<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    AttachmentEntity,
    PropertyChange,
    TaskEntity
};

class DeleteAttachmentCmd implements CommandInterface
{
    protected $attachmentId;

    public function __construct(?string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
    }

    public function __serialize(): array
    {
        return [
            'attachmentId' => $this->attachmentId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->attachmentId = $data['attachmentId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $attachment = $commandContext
            ->getDbEntityManager()
            ->selectById(AttachmentEntity::class, $this->attachmentId);

        $commandContext
            ->getDbEntityManager()
            ->delete($attachment);

        if ($attachment->getContentId() !== null) {
            $commandContext
            ->getByteArrayManager()
            ->deleteByteArrayById($attachment->getContentId());
        }

        if ($attachment->getTaskId() !== null) {
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

    public function isRetryable(): bool
    {
        return false;
    }
}
