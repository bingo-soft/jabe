<?php

namespace Jabe\Impl;

use Jabe\Impl\Db\TenantCheck;
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Task\{
    TaskCountByCandidateGroupResultInterface,
    TaskReportInterface
};

class TaskReportImpl implements TaskReportInterface
{
    protected $commandExecutor;

    protected $tenantCheck;// = new TenantCheck();

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
        $this->tenantCheck = new TenantCheck();
    }

    public function createTaskCountByCandidateGroupReport(CommandContext $commandContext): array
    {
        return $commandContext
            ->getTaskReportManager()
            ->createTaskCountByCandidateGroupReport($this);
    }

    public function getTenantCheck(): TenantCheck
    {
        return $this->tenantCheck;
    }

    public function taskCountByCandidateGroup(): array
    {
        return $this->commandExecutor->execute(new TaskCountByCandidateGroupCmd($this));
    }
}
