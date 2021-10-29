<?php

namespace BpmPlatform\Engine\Impl\Language;

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
     * @return <code>true</code> if this node supports varargs.
     */
    public function isVarArgs(): bool;
}
