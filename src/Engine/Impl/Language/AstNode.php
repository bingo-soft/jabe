<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

abstract class AstNode implements ExpressionNode
{
    /**
     * evaluate and return the (optionally coerced) result.
     */
    public function getValue(Bindings $bindings, ELContext $context, string $type)
    {
        $value = $this->eval($bindings, $context);
        if ($type != null) {
            $value = $bindings->convert($value, $type);
        }
        return $value;
    }

    abstract public function appendStructure(string &$builder, Bindings $bindings): void;

    abstract public function eval(Bindings $bindings, ELContext $context);

    public function getStructuralId(Bindings $bindings): string
    {
        $builder = "";
        $this->appendStructure($builder, $bindings);
        return $builder;
    }
}
