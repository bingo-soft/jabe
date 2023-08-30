<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Form\StartFormDataInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};

class DelegateStartFormHandler extends DelegateFormHandler implements StartFormHandlerInterface
{
    public function __construct(StartFormHandlerInterface $formHandler, DeploymentEntity $deployment)
    {
        parent::__construct($formHandler, $deployment->getId());
    }

    public function createStartFormData(ProcessDefinitionEntity $processDefinition): StartFormDataInterface
    {
        $scope = $this;
        return $this->performContextSwitch(function () use ($scope, $processDefinition) {
            $invocation = new CreateStartFormInvocation($scope->formHandler, $processDefinition);
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($invocation);
            return $invocation->getInvocationResult();
        });
    }

    public function getFormHandler(): StartFormHandlerInterface
    {
        return $this->formHandler;
    }
}
