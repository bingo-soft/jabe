<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTaskCommentsCmd implements CommandInterface
{
    protected $taskId;

    public function __construct(?string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getCommentManager()
            ->findCommentsByTaskId($this->taskId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
