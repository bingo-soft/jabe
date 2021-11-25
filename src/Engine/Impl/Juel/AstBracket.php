<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\{
    ELContext,
    ELException
};

class AstBracket extends AstProperty
{
    protected $property;

    public function __construct(AstNode $base, AstNode $property, bool $lvalue, bool $strict)
    {
        parent::__construct($base, $lvalue, $strict);
        $this->property = $property;
    }

    protected function getProperty(Bindings $bindings, ELContext $context)
    {
        return $this->property->eval($bindings, $context);
    }

    public function __toString()
    {
        return "[...]";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $this->getChild(0)->appendStructure($b, $bindings);
        $b .= "[";
        $this->getChild(1)->appendStructure($b, $bindings);
        $b .= "]";
    }

    public function getCardinality(): int
    {
        return 2;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 1 ? $this->property : parent::getChild($i);
    }
}
