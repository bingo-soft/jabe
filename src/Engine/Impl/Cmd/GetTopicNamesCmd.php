<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\ExternalTaskQueryImpl;
use Jabe\Engine\Impl\Interceptor\{
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
}
