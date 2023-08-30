<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTaskAttachmentCmd implements CommandInterface
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
        return $commandContext
            ->getAttachmentManager()
            ->findAttachmentByTaskIdAndAttachmentId($this->taskId, $this->attachmentId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
