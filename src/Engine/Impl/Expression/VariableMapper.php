<?php

namespace BpmPlatform\Engine\Impl\Expression;

abstract class VariableMapper
{
    /**
     * Resolves the specified variable name to a ValueExpression.
     *
     * @param variable
     *            The variable name
     * @return the ValueExpression assigned to the variable, null if there is no previous assignment
     *         to this variable.
     */
    abstract public function resolveVariable(string $variable): ?ValueExpression;

    /**
     * Assign a ValueExpression to an EL variable, replacing any previously assignment to the same
     * variable. The assignment for the variable is removed if the expression is null.
     *
     * @param variable
     *            The variable name
     * @param expression
     *            The ValueExpression to be assigned to the variable.
     * @return The previous ValueExpression assigned to this variable, null if there is no previous
     *         assignment to this variable.
     */
    abstract public function setVariable(string $variable, ValueExpression $expression): ValueExpression;
}
