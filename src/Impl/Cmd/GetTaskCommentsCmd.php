<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTaskCommentsCmd implements CommandInterface, \Serializable
{
    protected $taskId;

    public function __construct(?string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
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
