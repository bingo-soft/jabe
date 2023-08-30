<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Page;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUnlockedTimersByDuedateCmd implements CommandInterface
{
    protected $duedate;
    protected $page;

    public function __construct(?string $duedate, ?Page $page)
    {
        $this->duedate = $duedate;
        $this->page = $page;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return Context::getCommandContext()
            ->getJobManager()
            ->findUnlockedTimersByDuedate($this->duedate, $this->page);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
