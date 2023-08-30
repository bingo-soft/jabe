<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\TaskQueryImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetSubTasksCmd implements CommandInterface
{
    protected $parentTaskId;

    public function __construct(?string $parentTaskId)
    {
        $this->parentTaskId = $parentTaskId;
    }

    public function __serialize(): array
    {
        return [
            'parentTaskId' => $this->parentTaskId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->parentTaskId = $data['parentTaskId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return (new TaskQueryImpl())
            ->taskParentTaskId($this->parentTaskId)
            ->list();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
