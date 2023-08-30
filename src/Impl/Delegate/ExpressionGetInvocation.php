<?php

namespace Jabe\Impl\Delegate;

use Jabe\Delegate\BaseDelegateExecutionInterface;
use El\{
    ELContext,
    ValueExpression
};

class ExpressionGetInvocation extends DelegateInvocation
{

    protected $valueExpression;
    protected $elContext;

    public function __construct(ValueExpression $valueExpression, ELContext $elContext, ?BaseDelegateExecutionInterface $contextExecution = null)
    {
        parent::__construct($contextExecution, null);
        $this->valueExpression = $valueExpression;
        $this->elContext = $elContext;
    }

    protected function invoke(): void
    {
        $this->invocationResult = $this->valueExpression->getValue($this->elContext);
    }
}
