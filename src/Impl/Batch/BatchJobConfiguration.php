<?php

namespace Jabe\Impl\Batch;

use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;

class BatchJobConfiguration implements JobHandlerConfigurationInterface
{
    protected $configurationByteArrayId;

    public function __construct(?string $configurationByteArrayId)
    {
        $this->configurationByteArrayId = $configurationByteArrayId;
    }

    public function getConfigurationByteArrayId(): ?string
    {
        return $this->configurationByteArrayId;
    }

    public function toCanonicalString(): ?string
    {
        return $this->configurationByteArrayId;
    }
}
