<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Task\TaskCountByCandidateGroupResultInterface;
use Jabe\Impl\Util\ClassNameUtil;

class TaskCountByCandidateGroupResultEntity implements TaskCountByCandidateGroupResultInterface
{
    protected $taskCount;
    protected $groupName;

    public function getTaskCount(): int
    {
        return $this->taskCount;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setTaskCount(int $taskCount): void
    {
        $this->taskCount = $taskCount;
    }

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[taskCount=" . $this->taskCount
            . ", groupName='" . $this->groupName
            . ']';
    }
}
