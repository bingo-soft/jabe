<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Form\{
    FormFieldInterface,
    TaskFormDataInterface
};
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    TaskEntity
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Variable\Impl\VariableMapImpl;

class GetTaskFormVariablesCmd extends AbstractGetFormVariablesCmd
{
    public function __construct(string $taskId, array $variableNames, bool $deserializeObjectValues)
    {
        parent::__construct($taskId, $variableNames, $deserializeObjectValues);
    }

    public function execute(CommandContext $commandContext)
    {
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($this->resourceId);

        EnsureUtil::ensureNotNull("Cannot find task with id '" . $this->resourceId . "'.", "task", $task);

        $this->checkGetTaskFormVariables($task, $commandContext);

        $result = new VariableMapImpl();

        // first, evaluate form fields
        $taskDefinition = $task->getTaskDefinition();
        if ($taskDefinition != null) {
            $taskFormData = $taskDefinition->getTaskFormHandler()->createTaskForm($task);
            foreach ($taskFormData->getFormFields() as $formField) {
                if (empty($this->formVariableNames) || in_array($formField->getId(), $this->formVariableNames)) {
                    $result->put($formField->getId(), $this->createVariable($formField, $task));
                }
            }
        }

        // collect remaining variables from task scope and parent scopes
        $task->collectVariables($result, $this->formVariableNames, false, $this->deserializeObjectValues);

        return $result;
    }

    protected function checkGetTaskFormVariables(TaskEntity $task, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }
    }
}
