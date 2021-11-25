<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\{
    ELContext,
    ELException,
    MethodInfo,
    ValueReference
};

class AstText extends AstNode
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function isLiteralText(): bool
    {
        return true;
    }

    public function isLeftValue(): bool
    {
        return false;
    }

    public function isMethodInvocation(): bool
    {
        return false;
    }

    public function getType(Bindings $bindings, ELContext $context): ?string
    {
        return null;
    }

    public function isReadOnly(Bindings $bindings, ELContext $context): bool
    {
        return true;
    }

    public function setValue(Bindings $bindings, ELContext $context, $value): void
    {
        throw new ELException(LocalMessages::get("error.value.set.rvalue", $this->getStructuralId($bindings)));
    }

    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference
    {
        return null;
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->value;
    }

    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo
    {
        return null;
    }

    public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $paramValues = [])
    {
        return $returnType == null ? $this->value : $bindings->convert($this->value, $returnType);
    }

    public function __toString()
    {
        return "\"" . $this->value . "\"";
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $end = strlen($this->value) - 1;
        for ($i = 0; $i < $end; $i++) {
            $c = $this->value[$i];
            if (($c == '#' || $c == '$') && $this->value[$i + 1] == '{') {
                $b .= '\\';
            }
            $b .= $c;
        }
        if ($end >= 0) {
            $b .= $this->value[$end];
        }
    }

    public function getCardinality(): int
    {
        return 0;
    }

    public function getChild(int $i): ?AstNode
    {
        return null;
    }
}
