<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\AttachmentEntity;

class GetAttachmentCmd implements CommandInterface
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
        return $commandContext
            ->getDbEntityManager()
            ->selectById(AttachmentEntity::class, $this->attachmentId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
