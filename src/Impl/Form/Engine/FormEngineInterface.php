<?php

namespace Jabe\Impl\Form\Engine;

use Jabe\Form\{
    StartFormDataInterface,
    TaskFormDataInterface
};

interface FormEngineInterface
{
    public function getName(): ?string;
    public function renderStartForm(StartFormDataInterface $startForm);
    public function renderTaskForm(TaskFormDataInterface $taskForm);
}
