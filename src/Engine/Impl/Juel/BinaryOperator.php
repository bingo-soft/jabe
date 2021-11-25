<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

interface BinaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right);
}
