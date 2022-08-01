<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Page;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUnlockedTimersByDuedateCmd implements CommandInterface
{
    protected $duedate;
    protected $page;

    public function __construct(string $duedate, Page $page)
    {
        $this->duedate = $duedate;
        $this->page = $page;
    }

    public function execute(CommandContext $commandContext)
    {
        return Context::getCommandContext()
            ->getJobManager()
            ->findUnlockedTimersByDuedate($this->duedate, $this->page);
    }
}
