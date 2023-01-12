<?php

namespace Jabe\Impl\Batch\History;

use Jabe\Batch\History\HistoricBatchInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\DbEntityInterface;
use Jabe\Impl\History\Event\HistoryEvent;
use Jabe\Impl\Persistence\Entity\{
    HistoricIncidentManager,
    HistoricJobLogManager
};

class HistoricBatchEntity extends HistoryEvent implements HistoricBatchInterface, DbEntityInterface
{
    protected $id;
    protected $type;

    protected int $totalJobs = 0;
    protected int $batchJobsPerSeed = 0;
    protected int $invocationsPerBatchJob = 0;

    protected $seedJobDefinitionId;
    protected $monitorJobDefinitionId;
    protected $batchJobDefinitionId;

    protected $tenantId;
    protected $createUserId;

    protected $startTime;
    protected $endTime;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTotalJobs(): int
    {
        return $this->totalJobs;
    }

    public function setTotalJobs(int $totalJobs): void
    {
        $this->totalJobs = $totalJobs;
    }

    public function getBatchJobsPerSeed(): int
    {
        return $this->batchJobsPerSeed;
    }

    public function setBatchJobsPerSeed(int $batchJobsPerSeed): void
    {
        $this->batchJobsPerSeed = $batchJobsPerSeed;
    }

    public function getInvocationsPerBatchJob(): int
    {
        return $this->invocationsPerBatchJob;
    }

    public function setInvocationsPerBatchJob(int $invocationsPerBatchJob): void
    {
        $this->invocationsPerBatchJob = $invocationsPerBatchJob;
    }

    public function getSeedJobDefinitionId(): ?string
    {
        return $this->seedJobDefinitionId;
    }

    public function setSeedJobDefinitionId(?string $seedJobDefinitionId): void
    {
        $this->seedJobDefinitionId = $seedJobDefinitionId;
    }

    public function getMonitorJobDefinitionId(): ?string
    {
        return $this->monitorJobDefinitionId;
    }

    public function setMonitorJobDefinitionId(?string $monitorJobDefinitionId): void
    {
        $this->monitorJobDefinitionId = $monitorJobDefinitionId;
    }

    public function getBatchJobDefinitionId(): ?string
    {
        return $this->batchJobDefinitionId;
    }

    public function setBatchJobDefinitionId(?string $batchJobDefinitionId): void
    {
        $this->batchJobDefinitionId = $batchJobDefinitionId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCreateUserId(): ?string
    {
        return $this->createUserId;
    }

    public function setCreateUserId(?string $createUserId): void
    {
        $this->createUserId = $createUserId;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getPersistentState()
    {
        $persistentState = [];

        $persistentState["endTime"] = $this->endTime;

        return $persistentState;
    }

    public function delete(): void
    {
        $historicIncidentManager = Context::getCommandContext()->getHistoricIncidentManager();
        $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->seedJobDefinitionId);
        $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->monitorJobDefinitionId);
        $historicIncidentManager->deleteHistoricIncidentsByJobDefinitionId($this->batchJobDefinitionId);

        $historicJobLogManager = Context::getCommandContext()->getHistoricJobLogManager();
        $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->seedJobDefinitionId);
        $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->monitorJobDefinitionId);
        $historicJobLogManager->deleteHistoricJobLogsByJobDefinitionId($this->batchJobDefinitionId);

        Context::getCommandContext()->getHistoricBatchManager()->delete($this);
    }
}
