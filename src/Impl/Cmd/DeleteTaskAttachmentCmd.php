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
use Jabe\Impl\Util\EnsureUtil;

class DeleteTaskAttachmentCmd implements CommandInterface
{
    protected $attachmentId;
    protected $taskId;

    public function __construct(?string $taskId, ?string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
        $this->taskId = $taskId;
    }

    public function __serialize(): array
    {
        return [
            'attachmentId' => $this->attachmentId,
            'taskId' => $this->taskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->attachmentId = $data['attachmentId'];
        $this->taskId = $data['taskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $attachment = $commandContext
            ->getAttachmentManager()
            ->findAttachmentByTaskIdAndAttachmentId($this->taskId, $this->attachmentId);

        EnsureUtil::ensureNotNull("No attachment exist for task id '" . $this->taskId . " and attachmentId '" . $this->attachmentId . "'.", "attachment", $attachment);

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
        }

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
