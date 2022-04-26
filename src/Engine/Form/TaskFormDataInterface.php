<?php

namespace Jabe\Engine\Form;

use Jabe\Engine\Task\TaskInterface;

interface TaskFormDataInterface
{
    public function getTask(): TaskInterface;
}
