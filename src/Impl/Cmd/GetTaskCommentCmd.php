<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetTaskCommentCmd implements CommandInterface
{
    protected $taskId;
    protected $commentId;

    public function __construct(?string $taskId, ?string $commentId)
    {
        $this->taskId = $taskId;
        $this->commentId = $commentId;
    }

    public function __serialize(): array
    {
        return [
            'taskId' => $this->taskId,
            'commentId' => $this->commentId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->taskId = $data['taskId'];
        $this->commentId = $data['commentId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $this->taskId);
        EnsureUtil::ensureNotNull("commentId", "commentId", $this->commentId);

        return $commandContext
            ->getCommentManager()
            ->findCommentByTaskIdAndCommentId($this->taskId, $this->commentId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
