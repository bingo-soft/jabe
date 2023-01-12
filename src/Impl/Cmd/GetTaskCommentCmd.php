<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetTaskCommentCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $commentId;

    public function __construct(?string $taskId, ?string $commentId)
    {
        $this->taskId = $taskId;
        $this->commentId = $commentId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'commentId' => $this->commentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->commentId = $json->commentId;
    }

    public function execute(CommandContext $commandContext)
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
