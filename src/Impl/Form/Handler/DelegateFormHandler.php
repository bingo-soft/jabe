<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};
use Sax\Element;
use Jabe\Variable\VariableMapInterface;

abstract class DelegateFormHandler
{
    protected $deploymentId;
    protected $formHandler;

    public function __construct(FormHandlerInterface $formHandler, ?string $deploymentId)
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
        $targetProcessApplication = ProcessApplicationContextUtil::getTargetProcessApplication($this->deploymentId);

        if ($targetProcessApplication !== null) {
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
        $scope = $this;
        $this->performContextSwitch(function () use ($scope, $properties, $variableScope) {
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation(new SubmitFormVariablesInvocation($scope->formHandler, $properties, $variableScope));
            return null;
        });
    }

    abstract public function getFormHandler(): FormHandlerInterface;
}
