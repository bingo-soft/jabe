<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\{
    ELContext,
    ELException
};

class AstChoice extends AstRightValue
{
    private $question;
    private $yes;
    private $no;

    public function __construct(AstNode $question, AstNode $yes, AstNode $no)
    {
        $this->question = $question;
        $this->yes = $yes;
        $this->no = $no;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        $value = $bindings->convert($this->question->eval($bindings, $context), "boolean");
        return $value ? $this->yes->eval($bindings, $context) : $this->no->eval($bindings, $context);
    }

    public function __toString()
    {
        return "?";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $this->question->appendStructure($b, $bindings);
        $b .= " ? ";
        $this->yes->appendStructure($b, $bindings);
        $b .= " : ";
        $this->no->appendStructure($b, $bindings);
    }

    public function getCardinality(): int
    {
        return 3;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->question : ($i == 1 ? $this->yes : ($i == 2 ? $this->no : null));
    }
}
