<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    AttachmentEntity,
    ByteArrayEntity
};

class GetAttachmentContentCmd implements CommandInterface
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
        $dbEntityManger = $commandContext->getDbEntityManager();
        $attachment = $dbEntityManger->selectById(AttachmentEntity::class, $this->attachmentId);

        $contentId = $attachment->getContentId();
        if ($contentId === null) {
            return null;
        }

        $byteArray = $dbEntityManger->selectById(ByteArrayEntity::class, $contentId);
        $bytes = $byteArray->getBytes();

        return $bytes;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
