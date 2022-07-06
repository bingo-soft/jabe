<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    DelegateVariableMappingInterface,
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Engine\Impl\Core\Model\CallableElement;
use Jabe\Engine\Impl\Delegate\DelegateInvocation;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    SubProcessActivityBehaviorInterface
};
use Jabe\Engine\Impl\Util\ClassDelegateUtil;
use Jabe\Engine\Variable\VariableMapInterface;

abstract class CallableElementActivityBehavior extends AbstractBpmnActivityBehavior implements SubProcessActivityBehavior
{
    protected $variablesFilter = [
        MultiInstanceActivityBehavior::NUMBER_OF_INSTANCES,
        MultiInstanceActivityBehavior::NUMBER_OF_ACTIVE_INSTANCES,
        MultiInstanceActivityBehavior::NUMBER_OF_COMPLETED_INSTANCES
    ];

    protected $callableElement;

    /**
     * The expression which identifies the delegation for the variable mapping.
     */
    protected $expression;

    /**
     * The class name of the delegated variable mapping, which should be used.
     */
    protected $className;

    public function __construct($prop = null)
    {
        if (is_string($prop)) {
            $this->className = $className;
        } elseif ($prop instanceof ExpressionInterface) {
            $this->expression = $expression;
        }
    }

    protected function getDelegateVariableMapping($instance): ?DelegateVariableMappingInterface
    {
        if ($instance instanceof DelegateVariableMappingInterface) {
            return $instance;
        } else {
            /*throw LOG.missingDelegateVariableMappingParentClassException(
                    instance.getClass().getName(),
                    DelegateVariableMapping.class.getName());*/
        }
    }

    protected function resolveDelegation(ActivityExecutionInterface $execution): ?DelegateVariableMappingInterface
    {
        $delegate = $this->resolveDelegateClass($execution);
        return $delegate !== null ? $this->getDelegateVariableMapping($delegate) : null;
    }

    public function resolveDelegateClass(ActivityExecutionInterface $execution)
    {
        $targetProcessApplication = ProcessApplicationContextUtil::getTargetProcessApplication($execution);
        if ($ProcessApplicationContextUtil::requiresContextSwitch($targetProcessApplication)) {
            $scope = $this;
            return Context::executeWithinProcessApplication(function () use ($scope, $execution) {
                return $scope->resolveDelegateClass($execution);
            }, $targetProcessApplication, new InvocationContext($execution));
        } else {
            return $this->instantiateDelegateClass($execution);
        }
    }

    protected function instantiateDelegateClass(ActivityExecutionInterface $execution)
    {
        $delegate = null;
        if ($this->expression !== null) {
            $delegate = $expression->getValue($execution);
        } elseif ($this->className !== null) {
            $delegate = ClassDelegateUtil::instantiateDelegate($className, null);
        }
        return $delegate;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $variables = $this->getInputVariables($execution);

        $varMapping = $this->resolveDelegation($execution);
        if ($varMapping !== null) {
            $this->invokeVarMappingDelegation(new class ($execution, $variables, $varMapping) extends DelegateInvocation {
                private $executions;
                private $variables;
                private $varMapping;

                public function __construct($execution, $variables, $varMapping)
                {
                    $this->execution = $execution;
                    $this->variables = $variables;
                    $this->varMapping = $varMapping;
                }

                protected function invoke(): void
                {
                    $this->varMapping->mapInputVariables($this->execution, $this->variables);
                }
            });
        }

        $businessKey = $this->getBusinessKey($execution);
        $this->startInstance($execution, $variables, $businessKey);
    }

    public function passOutputVariables(ActivityExecutionInterface $execution, VariableScopeInterface $subInstance): void
    {
        // only data. no control flow available on this execution.
        $variables = $this->filterVariables($this->getOutputVariables($subInstance));
        $localVariables = $this->getOutputVariablesLocal($subInstance);

        $execution->setVariables($variables);
        $execution->setVariablesLocal($localVariables);

        $varMapping = $this->resolveDelegation($execution);
        if ($varMapping !== null) {
            $this->invokeVarMappingDelegation(new class ($execution, $varMapping, $subInstance) extends DelegateInvocation {
                private $executions;
                private $subInstance;
                private $varMapping;

                public function __construct($execution, $varMapping, $subInstance)
                {
                    $this->execution = $execution;
                    $this->varMapping = $varMapping;
                    $this->subInstance = $subInstance;
                }

                protected function invoke(): void
                {
                    $this->varMapping->mapOutputVariables($this->execution, $this->subInstance);
                }
            });
        }
    }

    protected function invokeVarMappingDelegation(DelegateInvocation $delegation): void
    {
        try {
            Context::getProcessEngineConfiguration()->getDelegateInterceptor()->handleInvocation($delegation);
        } catch (\Exception $ex) {
            throw new ProcessEngineException($ex->getMessage(), $ex);
        }
    }

    protected function filterVariables(VariableMapInterface $variables): VariableMapInterface
    {
        if ($variables !== null) {
            foreach ($this->variablesFilter as $key) {
                $variables->remove($key);
            }
        }
        return $variables;
    }

    public function completed(ActivityExecutionInterface $execution): void
    {
        // only control flow. no sub instance data available
        $this->leave($execution);
    }

    public function getCallableElement(): ?CallableElement
    {
        return $this->callableElement;
    }

    public function setCallableElement(CallableElement $callableElement): void
    {
        $this->callableElement = $callableElement;
    }

    protected function getBusinessKey(ActivityExecutionInterface $execution): ?string
    {
        return $this->getCallableElement()->getBusinessKey($execution);
    }

    protected function getInputVariables(ActivityExecutionInterface $callingExecution): VariableMapInterface
    {
        return $this->getCallableElement()->getInputVariables($callingExecution);
    }

    protected function getOutputVariables(VariableScopeInterface $calledElementScope): VariableMapInterface
    {
        return $this->getCallableElement()->getOutputVariables($calledElementScope);
    }

    protected function getOutputVariablesLocal(VariableScopeInterface $calledElementScope): VariableMapInterface
    {
        return $this->getCallableElement()->getOutputVariablesLocal($calledElementScope);
    }

    protected function getVersion(ActivityExecutionInterface $execution): int
    {
        return $this->getCallableElement()->getVersion($execution);
    }

    protected function getDeploymentId(ActivityExecutionInterface $execution): ?string
    {
        return $this->getCallableElement()->getDeploymentId();
    }

    protected function getBinding(): ?string
    {
        return $this->getCallableElement()->getBinding();
    }

    protected function isLatestBinding(): bool
    {
        return $this->getCallableElement()->isLatestBinding();
    }

    protected function isDeploymentBinding(): bool
    {
        return $this->getCallableElement()->isDeploymentBinding();
    }

    protected function isVersionBinding(): bool
    {
        return $this->getCallableElement()->isVersionBinding();
    }

    abstract protected function startInstance(ActivityExecutionInterface $execution, VariableMapInterface $variables, ?string $businessKey = null): void;
}
