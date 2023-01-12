<?php

namespace Jabe\Impl\Batch;

class DeploymentMappings extends \ArrayObject
{
    protected int $overallIdCount = 0;

    public static function of(DeploymentMapping $mapping): DeploymentMappings
    {
        $mappings = new DeploymentMappings();
        $mappings->add($mapping);
        return $mappings;
    }

    public function add(DeploymentMapping $mapping): bool
    {
        $this->overallIdCount += $mapping->getCount();
        $this[] = $mapping;
        return true;
    }

    public function get(int $mappingIndex)
    {
        if (isset($this[$mappingIndex])) {
            return $this[$mappingIndex];
        } else {
            throw new \Exception(sprintf("Undefined array key %d", $mappingIndex));
        }
    }

    public function remove(int $mappingIndex): bool
    {
        if (isset($this[$mappingIndex])) {
            $this->overallIdCount -= $this[$mappingIndex]->getCount();
            unset($this[$mappingIndex]);
            return true;
        } else {
            return false;
        }
    }

    public function getOverallIdCount(): int
    {
        return $this->overallIdCount;
    }
}
