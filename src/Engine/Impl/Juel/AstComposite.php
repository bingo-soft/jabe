<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELContext;

class AstComposite extends AstRightValue
{
    private $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        $b = "";
        for ($i = 0; $i < $this->getCardinality(); $i++) {
            $b .= $bindings->convert($this->nodes[$i]->eval($bindings, $context), "string");
        }
        return $b;
    }

    public function __toString()
    {
        return "composite";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        for ($i = 0; $i < $this->getCardinality(); $i++) {
            $this->nodes[$i]->appendStructure($b, $bindings);
        }
    }

    public function getCardinality(): int
    {
        return count($this->nodes);
    }

    public function getChild(int $i): ?AstNode
    {
        if (array_key_exists($i, $this->nodes)) {
            return $this->nodes[$i];
        }
        return null;
    }
}
