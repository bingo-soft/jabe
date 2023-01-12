<?php

namespace Jabe\Impl\Scripting;

use Jabe\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};

class DynamicSourceExecutableScript extends DynamicExecutableScript
{
    public function __construct(?string $language, ExpressionInterface $scriptSourceExpression)
    {
        parent::__construct($scriptSourceExpression, $language);
    }

    public function getScriptSource(VariableScopeInterface $variableScope): ?string
    {
        return $this->evaluateExpression($variableScope);
    }
}
