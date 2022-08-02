<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\Engine\Delegate\PhpDelegateInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface,
    SignallableActivityBehaviorInterface
};

class ClassDelegateActivityBehavior extends AbstractBpmnActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $className;
    protected $fieldDeclarations = [];

    public function __construct(string $className, array $fieldDeclarations)
    {
        $this->className = $className;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $scope = $this;
        $this->executeWithErrorPropagation($execution, function () use ($scope, $execution) {
            $scope->getActivityBehaviorInstance($execution)->execute($execution);
            return null;
        });
    }

    // Signallable activity behavior
    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        $targetProcessApplication = ProcessApplicationContextUtil::getTargetProcessApplication($execution);
        $scope = $this;
        if (ProcessApplicationContextUtil::requiresContextSwitch($targetProcessApplication)) {
            Context::executeWithinProcessApplication(function () use ($scope, $execution, $signalName, $signalData) {
                $scope->signal($execution, $signalName, $signalData);
                return null;
            }, $targetProcessApplication, new InvocationContext($execution));
        } else {
            $this->doSignal($execution, $signalName, $signalData);
        }
    }

    protected function doSignal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        $activityBehaviorInstance = $this->getActivityBehaviorInstance($execution);

        if ($activityBehaviorInstance instanceof CustomActivityBehavior) {
            $behavior = $activityBehaviorInstance;
            $delegate = $behavior->getDelegateActivityBehavior();

            if (!($delegate instanceof SignallableActivityBehaviorInterface)) {
                //throw LOG.incorrectlyUsedSignalException(SignallableActivityBehavior.class.getName() );
            }
        }
        $this->executeWithErrorPropagation($execution, function () use ($activityBehaviorInstance, $execution, $signalName, $signalData) {
            $activityBehaviorInstance->signal($execution, $signalName, $signalData);
            return null;
        });
    }

    protected function getActivityBehaviorInstance(ActivityExecutionInterface $execution): ActivityBehaviorInterface
    {
        $delegateInstance = $this->instantiateDelegate($className, $this->fieldDeclarations);

        if ($delegateInstance instanceof ActivityBehaviorInterface) {
            return new CustomActivityBehavior($delegateInstance);
        } elseif ($delegateInstance instanceof PhpDelegateInterface) {
            return new ServiceTaskJavaDelegateActivityBehavior($delegateInstance);
        } else {
            /*throw LOG.missingDelegateParentClassException(
                delegateInstance.getClass().getName(),
                JavaDelegate.class.getName(),
                ActivityBehavior.class.getName()
            );*/
        }
    }
}
