<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    AttachmentEntity,
    PropertyChange,
    TaskEntity
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class DeleteTaskAttachmentCmd implements CommandInterface, \Serializable
{
    protected $attachmentId;
    protected $taskId;

    public function __construct(string $taskId, string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'attachmentId' => $this->attachmentId,
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->attachmentId = $json->attachmentId;
        $this->taskId = $json->taskId;
    }

    public function execute(CommandContext $commandContext)
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
}
