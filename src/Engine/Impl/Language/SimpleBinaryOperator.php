<?php

namespace BpmPlatform\Engine\Impl\Language;

abstract class SimpleBinaryOperator implements BinaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right)
    {
        return $this->apply($bindings, $left->eval($bindings, $context), $right->eval($bindings, $context));
    }

    abstract protected function apply(TypeConverter $converter, $o1, $o2);
}
