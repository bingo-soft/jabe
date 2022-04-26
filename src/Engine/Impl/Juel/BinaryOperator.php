<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELContext;

interface BinaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right);
}
