<?php

namespace Jabe\Engine\Impl\Form;

use Jabe\Engine\Form\TaskFormDataInterface;
use Jabe\Engine\Task\TaskInterface;

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
