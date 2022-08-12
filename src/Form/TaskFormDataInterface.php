<?php

namespace Jabe\Form;

use Jabe\Task\TaskInterface;

interface TaskFormDataInterface
{
    public function getTask(): TaskInterface;
}
