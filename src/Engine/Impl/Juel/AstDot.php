<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELContext;

class AstDot extends AstProperty
{
    protected $property;

    public function __construct(AstNode $base, string $property, bool $lvalue)
    {
        parent::__construct($base, $lvalue, true);
        $this->property = $property;
    }

    protected function getProperty(Bindings $bindings, ELContext $context)
    {
        return $this->property;
    }

    public function __toString()
    {
        return ". " . $this->property;
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $this->getChild[0]->appendStructure($b, $bindings);
        $b .= ".";
        $b .= $this->property;
    }

    public function getCardinality(): int
    {
        return 1;
    }
}
