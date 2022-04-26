<?php

namespace Jabe\Engine\Impl\Delegate;

use Jabe\Engine\Delegate\BaseDelegateExecutionInterface;
use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ValueExpression
};

class ExpressionGetInvocation extends DelegateInvocation
{

    protected $valueExpression;
    protected $elContext;

    public function __construct(ValueExpression $valueExpression, ELContext $elContext, BaseDelegateExecutionInterface $contextExecution)
    {
        parent::__construct($contextExecution, null);
        $this->valueExpression = $valueExpression;
        $this->elContext = $elContext;
    }

    protected function invoke(): void
    {
        $this->invocationResult = $this->valueExpression->getValue($elContext);
    }
}
