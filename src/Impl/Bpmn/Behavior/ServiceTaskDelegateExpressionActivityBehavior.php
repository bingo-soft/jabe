<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\Delegate\{
    ExpressionInterface,
    PhpDelegateInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Delegate\{
    ActivityBehaviorInvocation,
    PhpDelegateInvocation
};
use Jabe\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface,
    SignallableActivityBehaviorInterface
};

class ServiceTaskDelegateExpressionActivityBehavior extends TaskActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $expression;
    private $fieldDeclarations;

    public function __construct(ExpressionInterface $expression, array $fieldDeclarations)
    {
        $this->expression = $expression;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function signal(ActivityExecutionInterface $execution, ?string $signalName, $signalData): void
    {
        $targetProcessApplication = ProcessApplicationContextUtil::getTargetProcessApplication($execution);
        if (ProcessApplicationContextUtil::requiresContextSwitch($targetProcessApplication)) {
            $scope = $this;
            Context::executeWithinProcessApplication(function () use ($scope, $execution, $signalName, $signalData) {
                $scope->signal($execution, $signalName, $signalData);
                return null;
            }, $targetProcessApplication, new InvocationContext($execution));
        } else {
            $this->doSignal($execution, $signalName, $signalData);
        }
    }

    public function doSignal(ActivityExecutionInterface $execution, ?string $signalName, $signalData): void
    {
        $delegate = $this->expression->getValue($execution);
        $this->applyFieldDeclaration($this->fieldDeclarations, $delegate);
        $activityBehaviorInstance = $this->getActivityBehaviorInstance($execution, $delegate);

        if ($activityBehaviorInstance instanceof CustomActivityBehavior) {
            $behavior = $activityBehaviorInstance;
            $delegateActivityBehavior = $behavior->getDelegateActivityBehavior();

            if (!($delegateActivityBehavior instanceof SignallableActivityBehaviorInterface)) {
                // legacy behavior: do nothing when it is not a signallable activity behavior
                return;
            }
        }
        $this->executeWithErrorPropagation($execution, function ($activityBehaviorInstance, $execution, $signalName, $signalData) {
            $activityBehaviorInstance->signal($execution, $signalName, $signalData);
            return null;
        });
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $scope = $this;
        $this->executeWithErrorPropagation($execution, function ($scope, $execution) {
            // Note: we can't cache the result of the expression, because the
                // execution can change: eg. delegateExpression='${mySpringBeanFactory.randomSpringBean()}'
            $delegate = $scope->expression->getValue($execution);
            $scope->applyFieldDeclaration($scope->fieldDeclarations, $delegate);

            if ($delegate instanceof ActivityBehaviorInterface) {
                Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation(new ActivityBehaviorInvocation($delegate, $execution));
            } elseif ($delegate instanceof PhpDelegateInterface) {
                Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation(new PhpDelegateInvocation($delegate, $execution));
                $scope->leave($execution);
            } else {
                //throw LOG.resolveDelegateExpressionException(expression, ActivityBehavior.class, JavaDelegate.class);
            }
            return null;
        });
    }

    protected function getActivityBehaviorInstance(ActivityExecutionInterface $execution, $delegateInstance): ActivityBehaviorInterface
    {
        if ($delegateInstance instanceof ActivityBehaviorInterface) {
            return new CustomActivityBehavior($delegateInstance);
        } elseif ($delegateInstance instanceof PhpDelegateInterface) {
            return new ServiceTaskPhpDelegateActivityBehavior($delegateInstance);
        } else {
            //throw LOG.missingDelegateParentClassException(delegateInstance.getClass().getName(),
            //JavaDelegate.class.getName(), ActivityBehavior.class.getName());
        }
    }
}
