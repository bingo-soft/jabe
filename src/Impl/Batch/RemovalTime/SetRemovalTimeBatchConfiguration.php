<?php

namespace Jabe\Impl\Batch\RemovalTime;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappings
};

class SetRemovalTimeBatchConfiguration extends BatchConfiguration
{
    protected $removalTime;
    protected $hasRemovalTime;
    protected $isHierarchical;

    public function __construct(array $ids, DeploymentMappings $mappings = null)
    {
        parent::__construct($ids, $mappings);
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(string $removalTime): SetRemovalTimeBatchConfiguration
    {
        $this->removalTime = $removalTime;
        return $this;
    }

    public function hasRemovalTime(): bool
    {
        return $this->hasRemovalTime;
    }

    public function setHasRemovalTime(bool $hasRemovalTime): SetRemovalTimeBatchConfiguration
    {
        $this->hasRemovalTime = $hasRemovalTime;
        return $this;
    }

    public function isHierarchical(): bool
    {
        return $this->isHierarchical;
    }

    public function setHierarchical(bool $hierarchical): SetRemovalTimeBatchConfiguration
    {
        $this->isHierarchical = $hierarchical;
        return $this;
    }
}
