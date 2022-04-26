<?php

namespace Jabe\Engine\Impl\Juel;

interface TreeBuilder
{
    /**
     * Parse the given expression and create an abstract syntax tree for it.
     * @param expression expression string
     * @return tree corresponding to the given expression
     * @throws ELException on parse error
     */
    public function build(string $expression): Tree;
}
