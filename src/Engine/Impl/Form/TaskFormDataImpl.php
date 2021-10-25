<?php

namespace BpmPlatform\Engine\Impl\Form;

use BpmPlatform\Engine\Form\TaskFormDataInterface;
use BpmPlatform\Engine\Task\TaskInterface;

class TaskFormDataImpl extends FormDataImpl implements TaskFormDataInterface
{
    protected $task;

    // getters and setters //////////////////////////////////////////////////////

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function setTask(TaskInterface $task): void
    {
        $this->task = $task;
    }
}
