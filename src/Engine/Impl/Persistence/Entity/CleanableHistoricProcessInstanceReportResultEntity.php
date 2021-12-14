<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\CleanableHistoricProcessInstanceReportResultInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class CleanableHistoricProcessInstanceReportResultEntity implements CleanableHistoricProcessInstanceReportResultInterface
{
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionName;
    protected $processDefinitionVersion;
    protected $historyTimeToLive;
    protected $finishedProcessInstanceCount;
    protected $cleanableProcessInstanceCount;
    protected $tenantId;

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionName(): string
    {
        return $this->processDefinitionName;
    }

    public function setProcessDefinitionName(string $processDefinitionName): void
    {
        $this->processDefinitionName = $processDefinitionName;
    }

    public function getProcessDefinitionVersion(): int
    {
        return $this->processDefinitionVersion;
    }

    public function setProcessDefinitionVersion(int $processDefinitionVersion): void
    {
        $this->processDefinitionVersion = $processDefinitionVersion;
    }

    public function getHistoryTimeToLive(): int
    {
        return $this->historyTimeToLive;
    }

    public function setHistoryTimeToLive(int $historyTimeToLive): void
    {
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function getFinishedProcessInstanceCount(): int
    {
        return $this->finishedProcessInstanceCount;
    }

    public function setFinishedProcessInstanceCount(int $finishedProcessInstanceCount): void
    {
        $this->finishedProcessInstanceCount = $finishedProcessInstanceCount;
    }

    public function getCleanableProcessInstanceCount(): int
    {
        return $this->cleanableProcessInstanceCount;
    }

    public function setCleanableProcessInstanceCount(int $cleanableProcessInstanceCount): void
    {
        $this->cleanableProcessInstanceCount = $cleanableProcessInstanceCount;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[processDefinitionId = " . $this->processDefinitionId
            . ", processDefinitionKey = " . $this->processDefinitionKey
            . ", processDefinitionName = " . $this->processDefinitionName
            . ", processDefinitionVersion = " . $this->processDefinitionVersion
            . ", historyTimeToLive = " . $this->historyTimeToLive
            . ", finishedProcessInstanceCount = " . $this->finishedProcessInstanceCount
            . ", cleanableProcessInstanceCount = " . $this->cleanableProcessInstanceCount
            . ", tenantId = " . $this->tenantId
            . "]";
    }
}
