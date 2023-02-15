<?php

namespace Jabe\Impl\Bpmn\Listener;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    PhpDelegateInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Behavior\{
    BpmnBehaviorLogger,
    ServiceTaskPhpDelegateActivityBehavior
};
use Jabe\Impl\Bpmn\Delegate\ExecutionListenerInvocation;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ClassDelegate;
use Jabe\Impl\Util\ClassDelegateUtil;

class ClassDelegateExecutionListener extends ClassDelegate implements ExecutionListenerInterface
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    public function __construct(?string $className, array $fieldDeclarations)
    {
        parent::__construct($className, $fieldDeclarations);
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        $executionListenerInstance = $this->getExecutionListenerInstance();

        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ExecutionListenerInvocation($executionListenerInstance, $execution));
    }

    protected function getExecutionListenerInstance(): ExecutionListenerInterface
    {
        $delegateInstance = ClassDelegateUtil::instantiateDelegate($this->className, $this->fieldDeclarations);
        if ($delegateInstance instanceof ExecutionListenerInterface) {
            return $this->delegateInstance;
        } elseif ($delegateInstance instanceof PhpDelegateInterface) {
            return new ServiceTaskPhpDelegateActivityBehavior($delegateInstance);
        } else {
            //throw LOG.missingDelegateParentClassException(delegateInstance.getClass().getName(),
            //ExecutionListener.class.getName(), JavaDelegate.class.getName());
        }
    }
}
