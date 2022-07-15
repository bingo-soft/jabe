<?php

namespace Jabe\Engine\Impl\Batch;

class SetRetriesBatchConfiguration extends BatchConfiguration
{
    protected $retries;

    public function __construct(array $ids, $mappingsOrRetries, int $retries)
    {
        if ($mappingsOrRetries instanceof DeploymentMappings) {
            parent::__construct($ids, $mappingsOrRetries);
            $this->retries = $retries;
        } elseif (is_int($mappingsOrRetries)) {
            parent::__construct($ids);
            $this->retries = $mappingsOrRetries;
        }
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function setRetries(int $retries): void
    {
        $this->retries = $retries;
    }
}
