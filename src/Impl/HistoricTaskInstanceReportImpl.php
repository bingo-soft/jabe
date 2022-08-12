<?php

namespace Jabe\Impl;

use Jabe\Exception\NotValidException;
use Jabe\History\{
    DurationReportResultInterface,
    HistoricTaskInstanceReportInterface,
    HistoricTaskInstanceReportResultInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\TenantCheck;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;

class HistoricTaskInstanceReportImpl implements HistoricTaskInstanceReportInterface
{
    protected $completedAfter;
    protected $completedBefore;

    protected $durationPeriodUnit;

    protected $commandExecutor;

    protected $tenantCheck;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->commandExecutor = $commandExecutor;
        $this->tenantCheck = new TenantCheck();
    }

    public function countByProcessDefinitionKey(): array
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext === null) {
            return $this->commandExecutor->execute(new HistoricTaskInstanceCountByProcessDefinitionKey($this));
        } else {
            return $this->executeCountByProcessDefinitionKey($commandContext);
        }
    }

    protected function executeCountByProcessDefinitionKey(CommandContext $commandContext): array
    {
        return $commandContext->getTaskReportManager()
            ->selectHistoricTaskInstanceCountByProcDefKeyReport($this);
    }

    public function countByTaskName(): array
    {
        $commandContext = Context::getCommandContext();

        if ($commandContext === null) {
            return $this->commandExecutor->execute(new HistoricTaskInstanceCountByNameCmd($this));
        } else {
            return $this->executeCountByTaskName($commandContext);
        }
    }

    protected function executeCountByTaskName(CommandContext $commandContext): array
    {
        return $commandContext->getTaskReportManager()
            ->selectHistoricTaskInstanceCountByTaskNameReport($this);
    }

    public function duration(string $periodUnit): array
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "periodUnit", $periodUnit);
        $this->durationPeriodUnit = $periodUnit;

        $commandContext = Context::getCommandContext();

        if ($commandContext === null) {
            return $this->commandExecutor->execute(new ExecuteDurationCmd($this));
        } else {
            return $this->executeDuration($commandContext);
        }
    }

    protected function executeDuration(CommandContext $commandContext): array
    {
        return $commandContext->getTaskReportManager()
            ->createHistoricTaskDurationReport($this);
    }

    public function getCompletedAfter(): string
    {
        return $this->completedAfter;
    }

    public function getCompletedBefore(): string
    {
        return $this->completedBefore;
    }

    public function completedAfter(string $completedAfter): HistoricTaskInstanceReportInterface
    {
        EnsureUril::ensureNotNull(NotValidException::class, "completedAfter", $completedAfter);
        $this->completedAfter = $completedAfter;
        return $this;
    }

    public function completedBefore(string $completedBefore): HistoricTaskInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "completedBefore", $completedBefore);
        $this->completedBefore = $completedBefore;
        return $this;
    }

    public function getTenantCheck(): TenantCheck
    {
        return $this->tenantCheck;
    }

    public function getReportPeriodUnitName(): string
    {
        return $this->durationPeriodUnit;
    }
}
