<?php

namespace Jabe\Impl\Batch\Message;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappings
};

class MessageCorrelationBatchConfiguration extends BatchConfiguration
{
    protected $messageName;

    public function __construct(array $ids, ?DeploymentMappings $mappings, string $messageName, string $batchId = null)
    {
        parent::__construct($ids, $mappings);
        $this->messageName = $messageName;
        $this->batchId = $batchId;
    }

    public function getMessageName(): string
    {
        return $this->messageName;
    }

    public function setMessageName(string $messageName): void
    {
        $this->messageName = $messageName;
    }
}
