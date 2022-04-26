<?php

namespace Jabe\Engine\Impl\Juel;

abstract class AstLiteral extends AstRightValue
{
    public function getCardinality(): int
    {
        return 0;
    }

    public function getChild(int $i): ?AstNode
    {
        return null;
    }
}
