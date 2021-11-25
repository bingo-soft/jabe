<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

interface UnaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $node);
}
