<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Form\TaskFormDataInterface;
use Jabe\Engine\Impl\Form\{
    FormRefImpl,
    FormDefinition,
    TaskFormDataImpl
};
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;

class DefaultTaskFormHandler extends DefaultFormHandler implements TaskFormHandlerInterface
{
    public function createTaskForm(TaskEntity $task): TaskFormDataInterface
    {
        $taskFormData = new TaskFormDataImpl();

        $taskDefinition = $task->getTaskDefinition();

        $formDefinition = $taskDefinition->getFormDefinition();
        $formKey = $formDefinition->getFormKey();
        $formDefinitionKey = $formDefinition->getFormDefinitionKey();
        $formDefinitionBinding = $formDefinition->getFormDefinitionBinding();
        $formDefinitionVersion = $formDefinition->getFormDefinitionVersion();

        if ($formKey !== null) {
            $formValue = $formKey->getValue($task);
            if ($formValue !== null) {
                $taskFormData->setFormKey(strval($formValue));
            }
        } elseif ($formDefinitionKey !== null && $formDefinitionBinding !== null) {
            $formRefKeyValue = $formDefinitionKey->getValue($task);
            if ($formRefKeyValue !== null) {
                $ref = new FormRefImpl(strval($formRefKeyValue), $formDefinitionBinding);
                if ($formDefinitionBinding == self::FORM_REF_BINDING_VERSION && $formDefinitionVersion !== null) {
                    $formRefVersionValue = $formDefinitionVersion->getValue($task);
                    if ($formRefVersionValue !== null) {
                        $ref->setVersion(intval($formRefVersionValue));
                    }
                }
                $taskFormData->setFormRef($ref);
            }
        }

        $taskFormData->setDeploymentId($this->deploymentId);
        $taskFormData->setTask($task);
        $this->initializeFormProperties($taskFormData, $task->getExecution());
        $this->initializeFormFields($taskFormData, $task->getExecution());
        return $taskFormData;
    }
}
