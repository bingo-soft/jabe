<?php

namespace Jabe\Engine\Impl\Batch;

class DeploymentMappings extends \ArrayObject
{
    protected $overallIdCount;

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
        if (isset($this[$index])) {
            return $this[$index];
        } else {
            throw new \Exception(sprintf("Undefined array key %d", $index));
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
