<?php

namespace Jabe\Engine\Impl\Pvm\Runtime;

use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmTransitionInterface
};

class InstantiationStack
{
    protected $activities = [];
    protected $targetActivity;
    protected $targetTransition;

    public function __construct(array $activities, ?PvmActivityInterface $targetActivity = null, ?PvmTransitionInterface $targetTransition = null)
    {
        $this->activities = $activities;
        $this->targetActivity = $targetActivity;
        $this->targetTransition = $targetTransition;
    }

    public function getActivities(): array
    {
        return $this->activities;
    }

    public function getTargetTransition(): ?PvmTransitionInterface
    {
        return $this->targetTransition;
    }

    public function getTargetActivity(): ?PvmActivityInterface
    {
        return $this->targetActivity;
    }

    public function remove(int $id): PvmActivityInterface
    {
        $old = $this->activities[$id];
        array_splice($this->activities, $id, 1);
        return $old;
    }
}
