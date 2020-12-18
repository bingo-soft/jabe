<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ProcessBuilder;

interface ProcessInterface extends CallableElementInterface
{
    public function builder(): ProcessBuilder;

    public function getProcessType(): string;

    public function setProcessType(string $processType): void;

    public function isClosed(): bool;

    public function setClosed(bool $isClosed): void;

    public function isExecutable(): bool;

    public function setExecutable(bool $isExecutable): void;

    public function getAuditing(): AuditingInterface;

    public function setAuditing(AuditingInterface $auditing): void;

    public function getMonitoring(): MonitoringInterface;

    public function setMonitoring(MonitoringInterface $monitoring): void;

    public function getProperties(): array;

    public function getLaneSets(): array;

    public function getFlowElements(): array;

    public function getArtifacts(): array;

    public function getCorrelationSubscriptions(): array;

    public function getResourceRoles(): array;

    public function getSupports(): array;

    public function getCandidateStarterGroups(): string;

    public function setCandidateStarterGroups(string $candidateStarterGroups): void;

    public function getCandidateStarterGroupsList(): array;

    public function setCandidateStarterGroupsList(array $candidateStarterGroupsList): void;

    public function getCandidateStarterUsers(): string;

    public function setCandidateStarterUsers(string $candidateStarterUsers): void;

    public function getCandidateStarterUsersList(): array;

    public function setCandidateStarterUsersList(array $candidateStarterUsersList): void;

    public function getJobPriority(): string;

    public function setJobPriority(string $jobPriority): void;

    public function getTaskPriority(): string;

    public function setTaskPriority(string $taskPriority): void;

    public function getHistoryTimeToLiveString(): string;

    public function setHistoryTimeToLiveString(string $historyTimeToLive): void;

    public function isStartableInTasklist(): bool;

    public function setIsStartableInTasklist(bool $isStartableInTasklist): void;

    public function getVersionTag(): string;

    public function setVersionTag(string $versionTag): void;
}
