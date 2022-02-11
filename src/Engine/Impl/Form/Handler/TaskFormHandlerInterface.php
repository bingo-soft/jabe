<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Form\TaskFormDataInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\TaskEntity;

interface TaskFormHandlerInterface extends FormHandlerInterface
{
    public function createTaskForm(TaskEntity $task): TaskFormDataInterface;
}
