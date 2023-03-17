<?php

namespace Jabe\Impl;

use Jabe\History\HistoricActivityStatisticsQueryInterface;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;

class HistoricActivityStatisticsQueryImpl extends AbstractQuery implements HistoricActivityStatisticsQueryInterface
{
    protected $processDefinitionId;

    protected bool $includeFinished = false;
    protected bool $includeCanceled = false;
    protected bool $includeCompleteScope = false;
    protected bool $includeIncidents = false;

    protected $startedBefore;
    protected $startedAfter;
    protected $finishedBefore;
    protected $finishedAfter;

    protected $processInstanceIds = [];

    public function __construct(?string $processDefinitionId = null, ?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
        $this->processDefinitionId = $processDefinitionId;
    }

    public function includeFinished(): HistoricActivityStatisticsQueryInterface
    {
        $this->includeFinished = true;
        return $this;
    }

    public function includeCanceled(): HistoricActivityStatisticsQueryInterface
    {
        $this->includeCanceled = true;
        return $this;
    }

    public function includeCompleteScope(): HistoricActivityStatisticsQueryInterface
    {
        $this->includeCompleteScope = true;
        return $this;
    }

    public function includeIncidents(): HistoricActivityStatisticsQueryInterface
    {
        $this->includeIncidents = true;
        return $this;
    }

    public function startedAfter(?string $date): HistoricActivityStatisticsQueryInterface
    {
        $this->startedAfter = $date;
        return $this;
    }

    public function startedBefore(?string $date): HistoricActivityStatisticsQueryInterface
    {
        $this->startedBefore = $date;
        return $this;
    }

    public function finishedAfter(?string $date): HistoricActivityStatisticsQueryInterface
    {
        $this->finishedAfter = $date;
        return $this;
    }

    public function finishedBefore(?string $date): HistoricActivityStatisticsQueryInterface
    {
        $this->finishedBefore = $date;
        return $this;
    }

    public function processInstanceIdIn(array $processInstanceIds): HistoricActivityStatisticsQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceIds", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function orderByActivityId(): HistoricActivityStatisticsQueryInterface
    {
        return $this->orderBy(HistoricActivityStatisticsQueryProperty::activityId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return
            $commandContext
            ->getHistoricStatisticsManager()
            ->getHistoricStatisticsCountGroupedByActivity($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return
            $commandContext
            ->getHistoricStatisticsManager()
            ->getHistoricStatisticsGroupedByActivity($this, $page);
    }

    protected function checkQueryOk(): void
    {
        parent::checkQueryOk();
        EnsureUtil::ensureNotNull("No valid process definition id supplied", "processDefinitionId", $this->processDefinitionId);
    }

    // getters /////////////////////////////////////////////////

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function isIncludeFinished(): bool
    {
        return $this->includeFinished;
    }

    public function isIncludeCanceled(): bool
    {
        return $this->includeCanceled;
    }

    public function isIncludeCompleteScope(): bool
    {
        return $this->includeCompleteScope;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function isIncludeIncidents(): bool
    {
        return $this->includeIncidents;
    }
}
