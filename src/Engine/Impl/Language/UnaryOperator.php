<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

interface UnaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $node);
}
