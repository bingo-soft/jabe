<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\FormServiceInterface;
use Jabe\Engine\Form\{
    StartFormDataInterface,
    TaskFormDataInterface
};
use Jabe\Engine\Impl\Cmd\{
    GetDeployedStartFormCmd,
    GetFormKeyCmd,
    GetRenderedStartFormCmd,
    GetRenderedTaskFormCmd,
    GetStartFormCmd,
    GetStartFormVariablesCmd,
    GetTaskFormCmd,
    GetTaskFormVariablesCmd,
    SubmitStartFormCmd,
    SubmitTaskFormCmd
};
use Jabe\Engine\Runtime\ProcessInstanceInterface;
use Jabe\Engine\Variable\VariableMapInterface;

class FormServiceImpl extends ServiceImpl implements FormServiceInterface
{
    public function getRenderedStartForm(string $processDefinitionId, ?string $engineName = null)
    {
        return $this->commandExecutor->execute(new GetRenderedStartFormCmd($processDefinitionId, $engineName));
    }

    public function getRenderedTaskForm(string $taskId, ?string $engineName = null)
    {
        return $this->commandExecutor->execute(new GetRenderedTaskFormCmd($taskId, $engineName));
    }

    public function getStartFormData(string $processDefinitionId): StartFormDataInterface
    {
        return $this->commandExecutor->execute(new GetStartFormCmd($processDefinitionId));
    }

    public function getTaskFormData(string $taskId): TaskFormDataInterface
    {
        return $this->commandExecutor->execute(new GetTaskFormCmd($taskId));
    }

    public function submitStartFormData(string $processDefinitionId, ?string $businessKey, array $properties): ProcessInstanceInterface
    {
        return $this->commandExecutor->execute(new SubmitStartFormCmd($processDefinitionId, $businessKey, $properties));
    }

    public function submitStartForm(string $processDefinitionId, ?string $businessKey, array $properties): ProcessInstanceInterface
    {
        return $this->commandExecutor->execute(new SubmitStartFormCmd($processDefinitionId, $businessKey, $properties));
    }

    public function submitTaskFormData(string $taskId, array $properties): void
    {
        $this->submitTaskForm($taskId, $properties);
    }

    public function submitTaskForm(string $taskId, array $properties): void
    {
        $this->commandExecutor->execute(new SubmitTaskFormCmd($taskId, $properties, false, false));
    }

    public function submitTaskFormWithVariablesInReturn(string $taskId, array $properties, bool $deserializeValues): VariableMapInterface
    {
        return $this->commandExecutor->execute(new SubmitTaskFormCmd($taskId, $properties, true, $deserializeValues));
    }

    public function getStartFormKey(string $processDefinitionId): string
    {
        return $this->commandExecutor->execute(new GetFormKeyCmd($processDefinitionId));
    }

    public function getTaskFormKey(string $processDefinitionId, string $taskDefinitionKey): string
    {
        return $this->commandExecutor->execute(new GetFormKeyCmd($processDefinitionId, $taskDefinitionKey));
    }

    public function getStartFormVariables(string $processDefinitionId, array $formVariables = [], bool $deserializeObjectValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetStartFormVariablesCmd($processDefinitionId, $formVariables, $deserializeObjectValues));
    }

    public function getTaskFormVariables(string $processDefinitionId, array $formVariables = [], bool $deserializeObjectValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetTaskFormVariablesCmd($taskId, $formVariables, $deserializeObjectValues));
    }

    public function getDeployedStartForm(string $processDefinitionId)
    {
        return $this->commandExecutor->execute(new GetDeployedStartFormCmd($processDefinitionId));
    }

    public function getDeployedTaskForm(string $taskId)
    {
        return $this->commandExecutor->execute(new GetDeployedTaskFormCmd($taskId));
    }
}
