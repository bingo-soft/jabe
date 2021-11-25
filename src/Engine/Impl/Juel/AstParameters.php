<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

class AstParameters extends AstRightValue
{
    private $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        $result = [];
        for ($i = 0; $i < count($this->nodes); $i++) {
            $result[] = $this->nodes[$i]->eval($bindings, $context);
        }
        return $result;
    }

    public function __toString()
    {
        return "(...)";
    }

    public function appendStructure(string &$builder, Bindings $bindings): void
    {
        $builder .= "(";
        for ($i = 0; $i < count($this->nodes); $i++) {
            if ($i > 0) {
                $builder .= ", ";
            }
            $this->nodes[$i]->appendStructure($builder, $bindings);
        }
        $builder .= ")";
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
