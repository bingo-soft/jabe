<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ExternalTaskQueryImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTopicNamesCmd implements CommandInterface
{
    protected $externalTaskQuery;

    public function __construct(bool $withLockedTasks, bool $withUnlockedTasks, bool $withRetriesLeft)
    {
        $this->externalTaskQuery = new ExternalTaskQueryImpl();
        if ($withLockedTasks) {
            $this->externalTaskQuery->locked();
        }
        if ($withUnlockedTasks) {
            $this->externalTaskQuery->notLocked();
        }
        if ($withRetriesLeft) {
            $this->externalTaskQuery->withRetriesLeft();
        }
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getExternalTaskManager()
            ->selectTopicNamesByQuery($this->externalTaskQuery);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
