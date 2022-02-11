<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Form\TaskFormDataInterface;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    TaskEntity
};

class DelegateTaskFormHandler extends DelegateFormHandler implements TaskFormHandlerInterface
{
    public function __construct(TaskFormHandlerInterface $formHandler, DeploymentEntity $deployment)
    {
        parent::__construct($formHandler, $deployment->getId());
    }

    public function createTaskForm(TaskEntity $task): TaskFormDataInterface
    {
        $scope = $this;
        return $this->performContextSwitch(function () use ($scope, $task) {
            $invocation = new CreateTaskFormInvocation($formHandler, $task);
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($invocation);
            return $invocation->getInvocationResult();
        });
    }

    public function getFormHandler(): FormHandlerInterface
    {
        return $this->formHandler;
    }
}
