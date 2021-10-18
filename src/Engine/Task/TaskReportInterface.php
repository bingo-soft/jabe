<?php

namespace BpmPlatform\Engine\Task;

interface TaskReportInterface
{
    public function taskCountByCandidateGroup(): array;
}
