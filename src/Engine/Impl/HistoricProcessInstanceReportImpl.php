<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\History\HistoricProcessInstanceReportInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\TenantCheck;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\{
    CompareUtil,
    EnsureUtil
};

class HistoricProcessInstanceReportImpl implements HistoricProcessInstanceReportInterface
{
    protected $startedAfter;
    protected $startedBefore;
    protected $processDefinitionIdIn = [];
    protected $processDefinitionKeyIn = [];

    protected $durationPeriodUnit;

    protected $commandExecutor;

    protected $tenantCheck;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->tenantCheck = new TenantCheck();
        $this->commandExecutor = $commandExecutor;
    }

    public function startedAfter(string $startedAfter): HistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "startedAfter", $startedAfter);
        $this->startedAfter = $startedAfter;
        return $this;
    }

    public function startedBefore(string $startedBefore): HistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "startedBefore", $startedBefore);
        $this->startedBefore = $startedBefore;
        return $this;
    }

    public function processDefinitionIdIn(array $processDefinitionIds): HistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionIdIn", "processDefinitionIds", $processDefinitionIds);
        $this->processDefinitionIdIn = $processDefinitionIds;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeyIn", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeyIn = $processDefinitionKeys;
        return $this;
    }

    public function duration(string $periodUnit): array
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "periodUnit", $periodUnit);
        $this->durationPeriodUnit = $periodUnit;

        $commandContext = Context::getCommandContext();

        if ($commandContext === null) {
            return $commandExecutor->execute(new ExecuteDurationReportCmd($this));
        } else {
            return $this->executeDurationReport($commandContext);
        }
    }

    public function executeDurationReport(CommandContext $commandContext): array
    {
        $this->doAuthCheck($commandContext);

        if (CompareUtil::areNotInAscendingOrder($startedAfter, $startedBefore)) {
            return [];
        }

        return $commandContext
            ->getHistoricReportManager()
            ->selectHistoricProcessInstanceDurationReport($this);
    }

    protected function doAuthCheck(CommandContext $commandContext): void
    {
        // since a report does only make sense in context of historic
        // data, the authorization check will be performed here
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            if (empty($this->processDefinitionIdIn) && empty($this->processDefinitionKeyIn)) {
                $checker->checkReadHistoryAnyProcessDefinition();
            } else {
                $processDefinitionKeys = [];
                if (!empty($this->processDefinitionKeyIn)) {
                    $processDefinitionKeys = $this->processDefinitionKeyIn;
                }

                if (!empty($this->processDefinitionIdIn)) {
                    foreach ($this->processDefinitionIdIn as $processDefinitionId) {
                        $processDefinition = $commandContext->getProcessDefinitionManager()
                            ->findLatestProcessDefinitionById($processDefinitionId);

                        if ($processDefinition !== null && $processDefinition->getKey() !== null) {
                            $processDefinitionKeys[] = $processDefinition->getKey();
                        }
                    }
                }

                if (!empty($processDefinitionKeys)) {
                    foreach ($processDefinitionKeys as $processDefinitionKey) {
                        $checker->checkReadHistoryProcessDefinition($processDefinitionKey);
                    }
                }
            }
        }
    }

    public function getStartedAfter(): string
    {
        return $this->startedAfter;
    }

    public function getStartedBefore(): string
    {
        return $this->startedBefore;
    }

    public function getProcessDefinitionIdIn(): array
    {
        return $this->processDefinitionIdIn;
    }

    public function getProcessDefinitionKeyIn(): array
    {
        return $this->processDefinitionKeyIn;
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
