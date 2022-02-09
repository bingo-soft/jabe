<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Page;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\TimerEntity;

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
