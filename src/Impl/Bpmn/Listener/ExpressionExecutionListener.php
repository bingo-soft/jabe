<?php

namespace Jabe\Impl\Bpmn\Listener;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    ExpressionInterface
};

class ExpressionExecutionListener implements ExecutionListenerInterface
{
    protected $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        // Return value of expression is ignored
        $this->expression->getValue($execution);
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
