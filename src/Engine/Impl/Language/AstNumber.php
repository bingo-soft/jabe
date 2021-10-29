<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

class AstNumber extends AstLiteral
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->value;
    }

    public function __toString()
    {
        return strval($this->value);
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= strval($value);
    }
}
