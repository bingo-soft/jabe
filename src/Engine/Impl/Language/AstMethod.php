<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\{
    ELContext,
    ELException,
    MethodInfo,
    MethodNotFoundException,
    PropertyNotFoundException,
    ValueReference
};

class AstMethod extends AstNode
{
    private $property;
    private $params;

    public function __construct(AstProperty $property, AstParameters $params)
    {
        $this->property = $property;
        $this->params = $params;
    }

    public function isLiteralText(): bool
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

    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo
    {
        return null;
    }

    public function isLeftValue(): bool
    {
        return false;
    }

    public function isMethodInvocation(): bool
    {
        return true;
    }

    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference
    {
        return null;
    }

    public function appendStructure(string &$builder, Bindings $bindings): void
    {
        $this->property->appendStructure($builder, $bindings);
        $this->params->appendStructure($builder, $bindings);
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        return $this->invoke($bindings, $context);
    }

    public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $paramValues = [])
    {
        $base = $this->property->getPrefix()->eval($bindings, $context);
        if ($base == null) {
            throw new PropertyNotFoundException(LocalMessages::get("error.property.base.null", $property->getPrefix()));
        }
        $method = $property->getProperty($bindings, $context);
        if ($method == null) {
            throw new PropertyNotFoundException(LocalMessages::get("error.property.method.notfound", "null", $base));
        }
        $name = $bindings->convert($method, "string");
        $paramValues = $params->eval($bindings, $context);

        $context->setPropertyResolved(false);
        $result = $context->getELResolver()->invoke($context, $base, $name, $paramTypes, $paramValues);
        if (!$context->isPropertyResolved()) {
            throw new MethodNotFoundException(LocalMessages::get("error.property.method.notfound", $name, gettype($base)));
        }
        return $result;
    }

    public function getCardinality(): int
    {
        return 2;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->property : $i == 1 ? $this->params : null;
    }

    public function __toString()
    {
        return "<method>";
    }
}
