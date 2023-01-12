<?php

namespace Jabe\Variable\Context;

use Jabe\Variable\Value\TypedValueInterface;

interface VariableContextInterface
{
    /**
     * Resolve a value in this context.
     *
     * @param variableName the name of the variable to resolve.
     * @return TypedValueInterface the value of the variable or null in case the variable does not exist.
     */
    public function resolve(?string $variableName): ?TypedValueInterface;

    /**
     * Checks whether a variable with the given name is resolve through this context.
     *
     * @param variableName the name of the variable to check
     * @return bool - true if the variable is resolve.
     */
    public function containsVariable(?string $variableName): bool;

    /**
     * @return a set of all variable names resolvable through this Context.
     */
    public function keySet(): array;
}
