<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\ELContext;

interface BinaryOperator
{
    public function eval(Bindings $bindings, ELContext $context, AstNode $left, AstNode $right);
}
