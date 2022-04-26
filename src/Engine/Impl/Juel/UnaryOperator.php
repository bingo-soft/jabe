<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELContext;

interface UnaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $node);
}
