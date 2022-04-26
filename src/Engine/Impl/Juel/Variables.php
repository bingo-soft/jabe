<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ValueExpression,
    VariableMapper
};

class Variables extends VariableMapper
{
    private $map = [];

    public function resolveVariable(string $variable): ?ValueExpression
    {
        if (array_key_exists($variable, $this->map)) {
            return $this->map[$variable];
        }
        return null;
    }

    public function setVariable(string $variable, ValueExpression $expression): ValueExpression
    {
        $this->map[$variable] = $expression;
        return $expression;
    }
}
