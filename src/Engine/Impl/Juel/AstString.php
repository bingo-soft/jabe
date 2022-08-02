<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELContext;

class AstString extends AstLiteral
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->value;
    }

    public function __toString()
    {
        return "\"" . $this->value . "\"";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= "'";
        $length = strlen($this->value);
        for ($i = 0; $i < $length; $i++) {
            $c = $this->value[$i];
            if ($c == '\\' || $c == '\'') {
                $b .= '\\';
            }
            $b .= $c;
        }
        $b .= "'";
    }
}
