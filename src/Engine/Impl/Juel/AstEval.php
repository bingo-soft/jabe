<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    MethodInfo,
    ValueReference
};

class AstEval extends AstNode
{
    private $child;
    private $deferred;

    public function __construct(AstNode $child, bool $deferred)
    {
        $this->child = $child;
        $this->deferred = $deferred;
    }

    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    public function isLeftValue(): bool
    {
        return $this->getChild(0)->isLeftValue();
    }

    public function isMethodInvocation(): bool
    {
        return $this->getChild(0)->isMethodInvocation();
    }

    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference
    {
        return $this->child->getValueReference($bindings, $context);
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->child->eval($bindings, $context);
    }

    public function __toString()
    {
        return ($this->deferred ? "#" : "$") . "{...}";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= $this->deferred ? "#{" : "${";
        $this->child->appendStructure($b, $bindings);
        $b .= "}";
    }

    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo
    {
        return $this->child->getMethodInfo($bindings, $context, $returnType, $paramTypes);
    }

    public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $paramValues = [])
    {
        return $this->child->invoke($bindings, $context, $returnType, $paramTypes, $paramValues);
    }

    public function getType(Bindings $bindings, ELContext $context): ?string
    {
        return $this->child->getType($bindings, $context);
    }

    public function isLiteralText(): bool
    {
        return $this->child->isLiteralText();
    }

    public function isReadOnly(Bindings $bindings, ELContext $context): bool
    {
        return $this->child->isReadOnly($bindings, $context);
    }

    public function setValue(Bindings $bindings, ELContext $context, $value): void
    {
        $this->child->setValue($bindings, $context, $value);
    }

    public function getCardinality(): int
    {
        return 1;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->child : null;
    }
}
