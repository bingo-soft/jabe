<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Listener;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    PhpDelegateInterface
};
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Behavior\{
    BpmnBehaviorLogger,
    ServiceTaskPhpDelegateActivityBehavior,
    ExecutionListenerInvocation
};
use BpmPlatform\Engine\Impl\Bpmn\Parser\FieldDeclaration;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Delegate\ClassDelegate;

class ClassDelegateExecutionListener extends ClassDelegate implements ExecutionListenerInterface
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    public function __construct(string $className, array $fieldDeclarations)
    {
        parent::__construct($className, $fieldDeclarations);
    }

    public function notify(DelegateExecutionInterface $execution): void
    {
        $executionListenerInstance = $this->getExecutionListenerInstance();

        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ExecutionListenerInvocation($executionListenerInstance, $execution));
    }

    protected function getExecutionListenerInstance(): ExecutionListenerInterface
    {
        $delegateInstance = $this->instantiateDelegate($this->className, $this->fieldDeclarations);
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
