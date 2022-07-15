<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\Persistence\Entity\ByteArrayEntity;

class BatchJobContext
{
    protected $batch;
    protected $configuration;

    public function __construct(BatchEntity $batchEntity, ByteArrayEntity $configuration)
    {
        $this->batch = $batchEntity;
        $this->configuration = $configuration;
    }

    public function getBatch(): BatchEntity
    {
        return $this->batch;
    }

    public function setBatch(BatchEntity $batch): void
    {
        $this->batch = $batch;
    }

    public function getConfiguration(): ByteArrayEntity
    {
        return $this->configuration;
    }

    public function setConfiguration(ByteArrayEntity $configuration): void
    {
        $this->configuration = $configuration;
    }
}
