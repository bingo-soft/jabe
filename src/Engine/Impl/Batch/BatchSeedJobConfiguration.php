<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\JobExecutor\JobHandlerConfigurationInterface;

class BatchSeedJobConfiguration implements JobHandlerConfigurationInterface
{
    protected $batchId;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function __toString()
    {
        return $this->batchId;
    }

    public function toCanonicalString(): string
    {
        return $this->batchId;
    }
}
