<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

class AstNested extends AstRightValue
{
    private $child;

    public function __construct(AstNode $child)
    {
        $this->child = $child;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->child->eval($bindings, $context);
    }

    public function __toString()
    {
        return "(...)";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= "(";
        $this->child->appendStructure($b, $bindings);
        $b .= ")";
    }

    public function getCardinality(): int
    {
        return 1;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->child : null;
    }
}
