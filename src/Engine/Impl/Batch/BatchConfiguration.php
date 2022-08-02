<?php

namespace Jabe\Engine\Impl\Batch;

class BatchConfiguration
{
    protected $ids = [];
    protected $idMappings;
    protected $failIfNotExists;
    protected $batchId;

    public function __construct(array $ids, $failIfNotExistsOrMappings = null, $failIfNotExistsOrBatchId = null)
    {
        $this->ids = $ids;
        if ($failIfNotExistsOrMappings === null && $failIfNotExistsOrBatchId == null) {
            $this->failIfNotExists = true;
        } elseif (is_bool($failIfNotExistsOrMappings)) {
            $this->failIfNotExists = $failIfNotExistsOrMappings;
        } elseif ($failIfNotExistsOrMappings instanceof DeploymentMappings) {
            $this->idMappings = $failIfNotExistsOrMappings;
            if ($failIfNotExistsOrBatchId === null) {
                $this->failIfNotExists = true;
            } elseif (is_bool($failIfNotExistsOrBatchId)) {
                $this->failIfNotExists = $failIfNotExistsOrBatchId;
            } elseif (is_string($failIfNotExistsOrBatchId)) {
                $this->batchId = $failIfNotExistsOrBatchId;
            }
        }
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }

    public function getIdMappings(): DeploymentMappings
    {
        return $this->idMappings;
    }

    public function setIdMappings(DeploymentMappings $idMappings): void
    {
        $this->idMappings = $idMappings;
    }

    public function isFailIfNotExists(): bool
    {
        return $this->failIfNotExists;
    }

    public function setFailIfNotExists(bool $failIfNotExists): void
    {
        $this->failIfNotExists = $failIfNotExists;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function setBatchId(string $batchId): void
    {
        $this->batchId = $batchId;
    }
}
