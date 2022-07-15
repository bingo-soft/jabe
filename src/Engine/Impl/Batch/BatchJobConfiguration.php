<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\JobExecutor\JobHandlerConfigurationInterface;

class BatchJobConfiguration implements JobHandlerConfigurationInterface
{
    protected $configurationByteArrayId;

    public function __construct(string $configurationByteArrayId)
    {
        $this->configurationByteArrayId = $configurationByteArrayId;
    }

    public function getConfigurationByteArrayId(): string
    {
        return $this->configurationByteArrayId;
    }

    public function toCanonicalString(): string
    {
        return $this->configurationByteArrayId;
    }
}
