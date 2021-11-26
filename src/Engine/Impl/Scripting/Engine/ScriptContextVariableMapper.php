<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Impl\Util\Scripting\ScriptContextInterface;
use BpmPlatform\Engine\Impl\Util\El\{
    ExpressionFactory,
    ValueExpression,
    VariableMapper
};

class ScriptContextVariableMapper extends VariableMapper
{
    private $expressionFactory;

    private $scriptContext;

    public function __construct(ExpressionFactory $expressionFactory, ScriptContextInterface $scriptCtx)
    {
        $this->expressionFactory = $expressionFactory;
        $this->scriptContext = $scriptCtx;
    }

    public function resolveVariable(string $variableName): ValueExpression
    {
        $scope = $this->scriptContext->getAttributesScope($variableName);
        if ($scope != -1) {
            $value = $this->scriptContext->getAttribute($variableName, $scope);
            if ($value instanceof ValueExpression) {
                // Just return the existing ValueExpression
                return $value;
            } else {
                // Create a new ValueExpression based on the variable value
                return $this->expressionFactory->createValueExpression($value, "object");
            }
        }
        return null;
    }

    public function setVariable(string $name, ValueExpression $value): ValueExpression
    {
        $previousValue = $this->resolveVariable($name);
        $this->scriptContext->setAttribute($name, $value, ScriptContextInterface::ENGINE_SCOPE);
        return $previousValue;
    }
}
