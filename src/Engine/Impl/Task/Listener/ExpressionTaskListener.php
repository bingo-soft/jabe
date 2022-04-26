<?php

namespace Jabe\Engine\Impl\Task\Listener;

use Jabe\Engine\Delegate\{
    DelegateTaskInterface,
    ExpressionInterface,
    TaskListenerInterface
};

class ExpressionTaskListener implements TaskListenerInterface
{
    protected $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function notify(DelegateTaskInterface $delegateTask): void
    {
        $this->expression->getValue($delegateTask);
    }

    /**
     * returns the expression text for this task listener. Comes in handy if you want to
     * check which listeners you already have.
     */
    public function getExpressionText(): string
    {
        return $this->expression->getExpressionText();
    }
}
