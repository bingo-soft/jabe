<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Application\ProcessApplicationReferenceInterface;
use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};
use BpmPlatform\Engine\Impl\Util\Xml\Element;
use BpmPlatform\Engine\Variable\VariableMapInterface;

abstract class DelegateFormHandler
{
    protected $deploymentId;
    protected $formHandler;

    public function __construct(FormHandlerInterface $formHandler, string $deploymentId)
    {
        $this->formHandler = $formHandler;
        $this->deploymentId = $deploymentId;
    }

    public function parseConfiguration(Element $activityElement, DeploymentEntity $deployment, ProcessDefinitionEntity $processDefinition, BpmnParse $bpmnParse): void
    {
        // should not be called!
    }

    protected function performContextSwitch($callable)
    {
        $targetProcessApplication = ProcessApplicationContextUtil::getTargetProcessApplication($deploymentId);

        if ($targetProcessApplication != null) {
            $scope = $this;
            return Context::executeWithinProcessApplication(function () use ($scope, $callable) {
                return $scope->doCall($callable);
            }, $targetProcessApplication);
        } else {
            return $this->doCall($callable);
        }
    }

    protected function doCall($callable)
    {
        try {
            return $callable();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function submitFormVariables(VariableMapInterface $properties, VariableScopeInterface $variableScope): void
    {
        $this->performContextSwitch(function () use ($scope, $properties, $variableScope) {
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation(new SubmitFormVariablesInvocation($scope->formHandler, $properties, $variableScope));
            return null;
        });
    }

    abstract public function getFormHandler(): FormHandlerInterface;
}
