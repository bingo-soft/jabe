<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetProcessInstanceCommentsCmd implements CommandInterface
{
    protected $processInstanceId;

    public function __construct(?string $taskId)
    {
        $this->processInstanceId = $taskId;
    }

    public function __serialize(): array
    {
        return [
            'processInstanceId' => $this->processInstanceId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processInstanceId = $data['processInstanceId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getAttachmentManager()
            ->findCommentsByProcessInstanceId($this->processInstanceId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
