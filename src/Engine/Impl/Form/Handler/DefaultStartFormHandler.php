<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Delegate\ExpressionInterface;
use BpmPlatform\Engine\Form\StartFormDataInterface;
use BpmPlatform\Engine\Impl\Form\{
    FormRefImpl,
    FormDefinition,
    StartFormDataImpl
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};
use BpmPlatform\Engine\Variable\VariableMapInterface;

class DefaultStartFormHandler extends DefaultFormHandler implements StartFormHandlerInterface
{
    public function createStartFormData(ProcessDefinitionEntity $processDefinition): StartFormDataInterface
    {
        $startFormData = new StartFormDataImpl();

        $startFormDefinition = $processDefinition->getStartFormDefinition();
        $formKey = $startFormDefinition->getFormKey();
        $formDefinitionKey = $startFormDefinition->getFormDefinitionKey();
        $formDefinitionBinding = $startFormDefinition->getFormDefinitionBinding();
        $formDefinitionVersion = $startFormDefinition->getFormDefinitionVersion();

        if ($formKey != null) {
            $startFormData->setFormKey($formKey->getExpressionText());
        } elseif ($formDefinitionKey != null && $formDefinitionBinding != null) {
            $ref = new FormRefImpl($formDefinitionKey->getExpressionText(), $formDefinitionBinding);
            if ($formDefinitionBinding == self::FORM_REF_BINDING_VERSION && $formDefinitionVersion != null) {
                $ref->setVersion(intval($formDefinitionVersion->getExpressionText()));
            }
            $startFormData->setFormRef($ref);
        }

        $startFormData->setDeploymentId($deploymentId);
        $startFormData->setProcessDefinition($processDefinition);
        $this->initializeFormProperties($startFormData, null);
        $this->initializeFormFields($startFormData, null);
        return $startFormData;
    }

    public function submitStartFormData(ExecutionEntity $processInstance, VariableMapInterface $properties): ExecutionEntity
    {
        $this->submitFormVariables($properties, $processInstance);
        return $processInstance;
    }
}
