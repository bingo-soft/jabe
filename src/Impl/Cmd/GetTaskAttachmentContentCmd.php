<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ByteArrayEntity;

class GetTaskAttachmentContentCmd implements CommandInterface
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
            'taskId' => $this->taskId,
            'attachmentId' => $this->attachmentId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->attachmentId = $data['attachmentId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $attachment = $commandContext
            ->getAttachmentManager()
            ->findAttachmentByTaskIdAndAttachmentId($this->taskId, $this->attachmentId);

        if ($attachment === null) {
            return null;
        }

        $contentId = $attachment->getContentId();
        if ($contentId === null) {
            return null;
        }

        $byteArray = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $contentId);

        $bytes = $byteArray->getBytes();

        return $bytes;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
