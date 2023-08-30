<?php

namespace Jabe\Impl\Delegate;

use Jabe\Delegate\BaseDelegateExecutionInterface;
use El\{
    ELContext,
    ValueExpression
};

class ExpressionSetInvocation extends DelegateInvocation
{
    protected $valueExpression;
    protected $value;
    protected $elContext;

    public function __construct(ValueExpression $valueExpression, ELContext $elContext, $value, BaseDelegateExecutionInterface $contextExecution)
    {
        parent::__construct($contextExecution, null);
        $this->valueExpression = $valueExpression;
        $this->value = $value;
        $this->elContext = $elContext;
    }

    protected function invoke(): void
    {
        $this->valueExpression->setValue($this->elContext, $this->value);
    }
}
