<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Form\TaskFormDataInterface;
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;

interface TaskFormHandlerInterface extends FormHandlerInterface
{
    public function createTaskForm(TaskEntity $task): TaskFormDataInterface;
}
