<?php

namespace Jabe\Engine\Impl\El;

interface ExpressionManagerInterface
{
    /**
     * @param expression
     * @return a parsed expression
     */
    public function createExpression(string $expression): ExpressionInterface;

    /**
     * <p>
     * Adds a custom function to the expression manager that can be used in
     * expression evaluation later on. Ideally, use this in the setup phase of the
     * expression manager, i.e. before the first invocation of
     * {@link #createExpression(String) createExpression}.
     * </p>
     *
     * @param name
     * @param function
     */
    public function addFunction(string $name, \ReflectionMethod $function): void;
}
