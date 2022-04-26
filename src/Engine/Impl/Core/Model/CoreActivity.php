<?php

namespace Jabe\Engine\Impl\Core\Model;

use Jabe\Engine\Delegate\BaseDelegateExecutionInterface;
use Jabe\Engine\Impl\Core\Delegate\CoreActivityBehaviorInterface;
use Jabe\Engine\Impl\Core\Variable\Mapping\IoMapping;

abstract class CoreActivity extends CoreModelElement
{
    protected $ioMapping;

    public function __construct(string $id)
    {
        parent::__construct($id);
    }

    /** searches for the activity recursively */
    public function findActivity(string $activityId): ?CoreActivity
    {
        $localActivity = $this->getChildActivity($activityId);
        if ($localActivity != null) {
            return $localActivity;
        }
        foreach ($this->getActivities() as $activity) {
            $nestedActivity = $activity->findActivity($activityId);
            if ($nestedActivity != null) {
                return $nestedActivity;
            }
        }
        return null;
    }

    /** searches for the activity locally */
    abstract public function getChildActivity(string $activityId): ?CoreActivity;

    abstract public function createActivity(?string $activityId = null): CoreActivity;

    abstract public function getActivities(): array;

    abstract public function getActivityBehavior(): ?CoreActivityBehaviorInterface;

    public function getIoMapping(): ?IoMapping
    {
        return $this->ioMapping;
    }

    public function setIoMapping(IoMapping $ioMapping): void
    {
        $this->ioMapping = $ioMapping;
    }

    public function __toString()
    {
        return "Activity(" . $this->id . ")";
    }
}
