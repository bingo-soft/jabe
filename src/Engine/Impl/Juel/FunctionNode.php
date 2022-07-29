<?php

namespace Jabe\Engine\Impl\Juel;

interface FunctionNode extends Node
{
    /**
     * Get the full function name
     */
    public function getName(): string;

    /**
     * Get the unique index of this identifier in the expression (e.g. preorder index)
     */
    public function getIndex(): int;

    /**
     * Get the number of parameters for this function
     */
    public function getParamCount(): int;

    /**
     * @return bool true if this node supports varargs.
     */
    public function isVarArgs(): bool;
}
