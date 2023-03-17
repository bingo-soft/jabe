<?php

namespace Jabe\Impl\Bpmn\Listener;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    ExpressionInterface,
    PhpDelegateInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Behavior\BpmnBehaviorLogger;
use Jabe\Impl\Bpmn\Delegate\{
    ExecutionListenerInvocation,
    PhpDelegateInvocation
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Util\ClassDelegateUtil;

class DelegateExpressionExecutionListener implements ExecutionListenerInterface
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $expression;
    private $fieldDeclarations = [];

    public function __construct(ExpressionInterface $expression, array $fieldDeclarations)
    {
        $this->expression = $expression;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
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
    public function getExpressionText(): ?string
    {
        return $this->expression->getExpressionText();
    }
}
