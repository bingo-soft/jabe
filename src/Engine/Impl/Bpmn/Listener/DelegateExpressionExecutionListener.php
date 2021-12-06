<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Listener;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    ExpressionInterface,
    PhpDelegateInterface
};
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Behavior\BpmnBehaviorLogger;
use BpmPlatform\Engine\Impl\Bpmn\Delegate\{
    ExecutionListenerInvocation,
    PhpDelegateInvocation
};
use BpmPlatform\Engine\Impl\Bpmn\Parser\FieldDeclaration;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Util\ClassDelegateUtil;

class DelegateExpressionExecutionListener implements ExecutionListener
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $expression;
    private $fieldDeclarations = [];

    public function __construct(ExpressionInterface $expression, array $fieldDeclarations)
    {
        $this->expression = $expression;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function notify(DelegateExecutionInterface $execution): void
    {
        // Note: we can't cache the result of the expression, because the
        // execution can change: eg. delegateExpression='${mySpringBeanFactory.randomSpringBean()}'
        $delegate = $this->expression->getValue($execution);
        ClassDelegateUtil::applyFieldDeclaration($this->fieldDeclarations, $delegate);

        if ($delegate instanceof ExecutionListenerInterface) {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ExecutionListenerInvocation($delegate, $execution));
        } elseif ($delegate instanceof PhpDelegateInterface) {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new PhpDelegateInvocation($delegate, $execution));
        } else {
            //throw LOG.resolveDelegateExpressionException(expression, ExecutionListener.class, JavaDelegate.class);
        }
    }

    /**
     * returns the expression text for this execution listener. Comes in handy if you want to
     * check which listeners you already have.
     */
    public function getExpressionText(): string
    {
        return $this->expression->getExpressionText();
    }
}
