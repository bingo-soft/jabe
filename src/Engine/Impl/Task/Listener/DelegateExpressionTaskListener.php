<?php

namespace Jabe\Engine\Impl\Task\Listener;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    DelegateTaskInterface,
    ExpressionInterface,
    TaskListenerInterface,
    VariableScopeInterface
};
use Jabe\Engine\Impl\Bpmn\Parser\FieldDeclaration;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Task\Delegate\TaskListenerInvocation;

class DelegateExpressionTaskListener implements TaskListenerInterface
{
    protected $expression;
    private $fieldDeclarations = [];

    public function __construct(ExpressionInterface $expression, array $fieldDeclarations)
    {
        $this->expression = $expression;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function notify(DelegateTaskInterface $delegateTask): void
    {
        // Note: we can't cache the result of the expression, because the
        // execution can change: eg. delegateExpression='${mySpringBeanFactory.randomSpringBean()}'

        $variableScope = $delegateTask->getExecution();
        if ($variableScope === null) {
            //$variableScope = $delegateTask->getCaseExecution();
        }

        $delegate = $this->expression->getValue($variableScope);
        $this->applyFieldDeclaration($this->fieldDeclarations, $delegate);

        if ($delegate instanceof TaskListenerInterface) {
            try {
                Context::getProcessEngineConfiguration()
                    ->getDelegateInterceptor()
                    ->handleInvocation(new TaskListenerInvocation($delegate, $delegateTask));
            } catch (\Exception $e) {
                throw new ProcessEngineException("Exception while invoking TaskListener: " . $e->getMessage(), $e);
            }
        } else {
            throw new ProcessEngineException("Delegate expression " . $this->expression
                    . " did not resolve to an implementation of " . TaskListenerInterface::class);
        }
    }

    /**
     * returns the expression text for this task listener. Comes in handy if you want to
     * check which listeners you already have.
     */
    public function getExpressionText(): string
    {
        return $this->expression->getExpressionText();
    }

    public function getFieldDeclarations(): array
    {
        return $this->fieldDeclarations;
    }
}
