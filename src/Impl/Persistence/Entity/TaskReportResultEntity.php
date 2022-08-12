<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\HistoricTaskInstanceReportResultInterface;
use Jabe\Impl\Util\ClassNameUtil;

class TaskReportResultEntity implements HistoricTaskInstanceReportResultInterface
{
    protected $count;
    protected $processDefinitionKey;
    protected $processDefinitionId;
    protected $processDefinitionName;
    protected $taskName;
    protected $tenantId;

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionName(): string
    {
        return $this->processDefinitionName;
    }

    public function setProcessDefinitionName(string $processDefinitionName): void
    {
        $this->processDefinitionName = $processDefinitionName;
    }

    public function getTaskName(): string
    {
        return $this->taskName;
    }

    public function setTaskName(string $taskName): void
    {
        $this->taskName = $taskName;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
             . "[" .
            "count=" . $this->count .
            ", processDefinitionKey='" . $this->processDefinitionKey . '\'' .
            ", processDefinitionId='" . $this->processDefinitionId . '\'' .
            ", processDefinitionName='" . $this->processDefinitionName . '\'' .
            ", taskName='" . $this->taskName . '\'' .
            ", tenantId='" . $this->tenantId . '\'' .
            ']';
    }
}
