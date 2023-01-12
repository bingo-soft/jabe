<?php

namespace Jabe\Impl\Batch;

class DeploymentMapping
{
    protected const NULL_ID = '$NULL';

    protected ?string $deploymentId;
    protected int $count = 0;

    public function __construct(?string $deploymentId, int $count)
    {
        $this->deploymentId = $deploymentId == null ? self::NULL_ID : $deploymentId;
        $this->count = $count;
    }

    public function getDeploymentId(): ?string
    {
        return self::NULL_ID == $this->deploymentId ? null : $this->deploymentId;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getIds(array $ids): array
    {
        return array_slice($ids, 0, $this->count);
    }

    public function removeIds(int $numberOfIds): void
    {
        $this->count -= $numberOfIds;
    }

    public function __toString()
    {
        return sprintf('%s;%d', $this->deploymentId, $this->count);
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if (!($obj instanceof DeploymentMapping)) {
            return false;
        }
        return $this->count == $obj->count && $this->deploymentId == $obj->deploymentId;
    }
}
