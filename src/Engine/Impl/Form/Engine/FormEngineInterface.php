<?php

namespace Jabe\Engine\Impl\Form\Engine;

use Jabe\Engine\Form\{
    StartFormDataInterface,
    TaskFormDataInterface
};

interface FormEngineInterface
{
    public function getName(): string;
    public function renderStartForm(StartFormDataInterface $startForm);
    public function renderTaskForm(TaskFormDataInterface $taskForm);
}
