<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Form\TaskFormDataInterface;
use Jabe\Impl\Persistence\Entity\TaskEntity;

interface TaskFormHandlerInterface extends FormHandlerInterface
{
    public function createTaskForm(TaskEntity $task): TaskFormDataInterface;
}
