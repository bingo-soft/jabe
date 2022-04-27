<?php

namespace Jabe\Engine\Impl\Batch\Deletion;

use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappings
};

class DeleteProcessInstanceBatchConfiguration extends BatchConfiguration
{
    protected $deleteReason;
    protected $skipCustomListeners;
    protected $skipSubprocesses;

    public function __construct(array $ids, DeploymentMappings $mappings = null, string $deleteReason = null, bool $skipCustomListeners = true, bool $skipSubprocesses = true, bool $failIfNotExists = false)
    {
        parent::__construct($ids, $mappings);
        $this->deleteReason = $deleteReason;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipSubprocesses = $skipSubprocesses;
        $this->failIfNotExists = $failIfNotExists;
    }

    public function getDeleteReason(): ?string
    {
        return $this->deleteReason;
    }

    public function setDeleteReason(string $deleteReason): void
    {
        $this->deleteReason = $deleteReason;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function isSkipSubprocesses(): bool
    {
        return $this->skipSubprocesses;
    }

    public function setSkipSubprocesses(bool $skipSubprocesses): void
    {
        $this->skipSubprocesses = $skipSubprocesses;
    }
}
