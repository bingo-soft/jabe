<?php

namespace Jabe\Engine\Impl\Scripting;

use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};

class DynamicSourceExecutableScript extends DynamicExecutableScript
{
    public function __construct(string $language, ExpressionInterface $scriptSourceExpression)
    {
        parent::__construct($scriptSourceExpression, $language);
    }

    public function getScriptSource(VariableScopeInterface $variableScope): string
    {
        return $this->evaluateExpression($variableScope);
    }
}
