<?php

namespace Jabe\Impl\Form;

use Jabe\Form\TaskFormDataInterface;
use Jabe\Task\TaskInterface;

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
