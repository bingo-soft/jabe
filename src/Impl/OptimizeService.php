<?php

namespace Jabe\Impl;

use Jabe\History\{
    HistoricActivityInstanceInterface,
    HistoricProcessInstanceInterface,
    HistoricTaskInstanceInterface,
    HistoricVariableUpdateInterface,
    UserOperationLogEntryInterface
};
use Jabe\Impl\Cmd\Optimize\{
    OptimizeCompletedHistoricActivityInstanceQueryCmd,
    OptimizeCompletedHistoricIncidentsQueryCmd,
    OptimizeCompletedHistoricProcessInstanceQueryCmd,
    OptimizeCompletedHistoricTaskInstanceQueryCmd,
    //OptimizeHistoricDecisionInstanceQueryCmd,
    OptimizeHistoricIdentityLinkLogQueryCmd,
    OptimizeHistoricUserOperationsLogQueryCmd,
    OptimizeHistoricVariableUpdateQueryCmd,
    OptimizeOpenHistoricIncidentsQueryCmd,
    OptimizeRunningHistoricActivityInstanceQueryCmd,
    OptimizeRunningHistoricProcessInstanceQueryCmd,
    OptimizeRunningHistoricTaskInstanceQueryCmd
};

class OptimizeService extends ServiceImpl
{
    public function getCompletedHistoricActivityInstances(
        string $finishedAfter,
        string $finishedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeCompletedHistoricActivityInstanceQueryCmd($finishedAfter, $finishedAt, $maxResults)
        );
    }

    public function getRunningHistoricActivityInstances(
        string $startedAfter,
        string $startedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeRunningHistoricActivityInstanceQueryCmd($startedAfter, $startedAt, $maxResults)
        );
    }

    public function getCompletedHistoricTaskInstances(
        string $finishedAfter,
        string $finishedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeCompletedHistoricTaskInstanceQueryCmd($finishedAfter, $finishedAt, $maxResults)
        );
    }

    public function getRunningHistoricTaskInstances(
        string $startedAfter,
        string $startedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeRunningHistoricTaskInstanceQueryCmd($startedAfter, $startedAt, $maxResults)
        );
    }

    public function getHistoricUserOperationLogs(
        string $occurredAfter,
        string $occurredAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeHistoricUserOperationsLogQueryCmd($occurredAfter, $occurredAt, $maxResults)
        );
    }

    public function getHistoricIdentityLinkLogs(
        string $occurredAfter,
        string $occurredAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeHistoricIdentityLinkLogQueryCmd($occurredAfter, $occurredAt, $maxResults)
        );
    }

    public function getCompletedHistoricProcessInstances(
        string $finishedAfter,
        string $finishedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeCompletedHistoricProcessInstanceQueryCmd($finishedAfter, $finishedAt, $maxResults)
        );
    }

    public function getRunningHistoricProcessInstances(
        string $startedAfter,
        string $startedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeRunningHistoricProcessInstanceQueryCmd($startedAfter, $startedAt, $maxResults)
        );
    }

    public function getHistoricVariableUpdates(
        string $occurredAfter,
        string $occurredAt,
        bool $excludeObjectValues,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeHistoricVariableUpdateQueryCmd($occurredAfter, $occurredAt, $excludeObjectValues, $maxResults)
        );
    }

    public function getCompletedHistoricIncidents(
        string $finishedAfter,
        string $finishedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeCompletedHistoricIncidentsQueryCmd($finishedAfter, $finishedAt, $maxResults)
        );
    }

    public function getOpenHistoricIncidents(
        string $createdAfter,
        string $createdAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeOpenHistoricIncidentsQueryCmd($createdAfter, $createdAt, $maxResults)
        );
    }

    public function getHistoricDecisionInstances(
        string $evaluatedAfter,
        string $evaluatedAt,
        int $maxResults
    ): array {
        return $this->commandExecutor->execute(
            new OptimizeHistoricDecisionInstanceQueryCmd(evaluatedAfter, evaluatedAt, $maxResults)
        );
    }
}
