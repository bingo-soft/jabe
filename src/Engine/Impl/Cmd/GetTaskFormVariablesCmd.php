<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Form\{
    FormFieldInterface,
    TaskFormDataInterface
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\{
    TaskEntity
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

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
        if ($taskDefinition !== null) {
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
