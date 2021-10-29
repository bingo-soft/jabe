<?php

namespace BpmPlatform\Engine\Impl\Language;

abstract class SimpleUnaryOperator implements UnaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $node)
    {
        return $this->apply($bindings, $node->eval($bindings, $context));
    }

    abstract protected function apply(TypeConverter $converter, $o);
}
