<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

class AstBoolean extends AstLiteral
{
    private $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->value;
    }

    public function __toString()
    {
        return $value ? "true" : "false";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= $this->__toString();
    }
}
