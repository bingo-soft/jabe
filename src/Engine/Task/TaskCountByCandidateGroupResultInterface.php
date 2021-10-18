<?php

namespace BpmPlatform\Engine\Task;

interface TaskCountByCandidateGroupResultInterface
{
    /** The number of tasks for a specific group */
    public function getTaskCount(): int;

    /** The group which as the number of tasks */
    public function getGroupName(): string;
}
