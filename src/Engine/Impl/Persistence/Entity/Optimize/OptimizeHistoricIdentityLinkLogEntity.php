<?php

namespace Jabe\Engine\Impl\Persistence\Entity\Optimize;

use Jabe\Engine\Impl\Persistence\Entity\HistoricIdentityLinkLogEntity;

class OptimizeHistoricIdentityLinkLogEntity extends HistoricIdentityLinkLogEntity
{
    protected $processInstanceId;

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }
}
