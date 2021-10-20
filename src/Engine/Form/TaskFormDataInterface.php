<?php

namespace BpmPlatform\Engine\Form;

use BpmPlatform\Engine\Task\TaskInterface;

interface TaskFormDataInterface
{
    public function getTask(): TaskInterface;
}
