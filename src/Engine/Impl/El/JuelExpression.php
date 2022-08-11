<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Delegate\{
    ExpressionGetInvocation,
    ExpressionSetInvocation
};
use El\{
    ELContext,
    ELException,
    MethodNotFoundException,
    PropertyNotFoundException,
    ValueExpression
};

class JuelExpression implements ExpressionInterface
{
    protected $expressionText;
    protected $valueExpression;
    protected $expressionManager;

    public function __construct(ValueExpression $valueExpression, JuelExpressionManager $expressionManager, string $expressionText)
    {
        $this->valueExpression = $valueExpression;
        $this->expressionManager = $expressionManager;
        $this->expressionText = $expressionText;
    }

    public function getValue(VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution = null)
    {
        $elContext = $this->expressionManager->getElContext($variableScope);
        try {
            $invocation = new ExpressionGetInvocation($this->valueExpression, $elContext, $contextExecution);
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($invocation);
            return $invocation->getInvocationResult();
        } catch (PropertyNotFoundException $pnfe) {
            throw new ProcessEngineException("Unknown property used in expression: " . $this->expressionText, $pnfe);
        } catch (MethodNotFoundException $mnfe) {
            throw new ProcessEngineException("Unknown method used in expression: " . $this->expressionText, $mnfe);
        } catch (ELException $ele) {
            throw new ProcessEngineException("Error while evaluating expression: " . $this->expressionText, $ele);
        } catch (\Exception $e) {
            throw new ProcessEngineException("Error while evaluating expression: " . $this->expressionText, $e);
        }
    }

    public function setValue($value, VariableScopeInterface $variableScope, BaseDelegateExecutionInterface $contextExecution = null)
    {
        $elContext = $this->expressionManager->getElContext($variableScope);
        try {
            $invocation = new ExpressionSetInvocation($this->valueExpression, $elContext, $value, $contextExecution);
            Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw new ProcessEngineException("Error while evaluating expression: " . $this->expressionText, $e);
        }
    }

    public function __toString()
    {
        return $this->valueExpression->getExpressionString();
    }

    public function isLiteralText(): bool
    {
        return $this->valueExpression->isLiteralText();
    }

    public function getExpressionText(): string
    {
        return $this->expressionText;
    }
}
