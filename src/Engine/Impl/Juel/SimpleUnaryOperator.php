<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

abstract class SimpleUnaryOperator implements UnaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $node)
    {
        return $this->apply($bindings, $node->eval($bindings, $context));
    }

    abstract protected function apply(TypeConverter $converter, $o);
}
