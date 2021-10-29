<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

class AstNull extends AstLiteral
{
    public function eval(Bindings $bindings, ELContext $context)
    {
        return null;
    }

    public function __toString()
    {
        return "null";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= "null";
    }
}
