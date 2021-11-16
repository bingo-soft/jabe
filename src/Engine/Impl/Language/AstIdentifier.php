<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\{
    ELContext,
    ELException,
    MethodInfo,
    MethodNotFoundException,
    PropertyNotFoundException,
    ValueExpression,
    ValueReference
};

class AstIdentifier extends AstNode implements IdentifierNode
{
    private $name;
    private $index;

    public function __construct(string $name, int $index)
    {
        $this->name = $name;
        $this->index = $index;
    }

    public function getType(Bindings $bindings, ELContext $context): ?string
    {
        $expression = $bindings->getVariable($this->index);
        if ($expression != null) {
            return $expression->getType($context);
        }
        $context->setPropertyResolved(false);
        $result = $context->getELResolver()->getType($context, null, $this->name);
        if (!$context->isPropertyResolved()) {
            throw new PropertyNotFoundException(LocalMessages::get("error.identifier.property.notfound", $this->name));
        }
        return $result;
    }


    public function isLeftValue(): bool
    {
        return true;
    }

    public function isMethodInvocation(): bool
    {
        return false;
    }

    public function isLiteralText(): bool
    {
        return false;
    }

    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference
    {
        $expression = $bindings->getVariable($this->index);
        if ($expression != null) {
            return $expression->getValueReference($context);
        }
        return new ValueReference(null, $this->name);
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        $expression = $bindings->getVariable($this->index);
        if ($expression != null) {
            return $expression->getValue($context);
        }
        $context->setPropertyResolved(false);
        $result = $context->getELResolver()->getValue($context, null, $this->name);
        if (!$context->isPropertyResolved()) {
            throw new PropertyNotFoundException(LocalMessages::get("error.identifier.property.notfound", $this->name));
        }
        return $result;
    }

    public function setValue(Bindings $bindings, ELContext $context, $value): void
    {
        $expression = $bindings->getVariable($this->index);
        if ($expression != null) {
            $expression->setValue($context, $value);
            return;
        }
        $context->setPropertyResolved(false);
        $context->getELResolver()->setValue($context, null, $this->name, $value);
        if (!$context->isPropertyResolved()) {
            throw new PropertyNotFoundException(LocalMessages::get("error.identifier.property.notfound", $this->name));
        }
    }

    public function isReadOnly(Bindings $bindings, ELContext $context): bool
    {
        $expression = $bindings->getVariable($this->index);
        if ($expression != null) {
            return $expression->isReadOnly($context);
        }
        $context->setPropertyResolved(false);
        $result = $context->getELResolver()->isReadOnly($context, null, $this->name);
        if (!$context->isPropertyResolved()) {
            throw new PropertyNotFoundException(LocalMessages::get("error.identifier.property.notfound", $this->name));
        }
        return $result;
    }

    protected function getMethod(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): \ReflectionMethod
    {
        $value = $this->eval($bindings, $context);
        if ($value == null) {
            throw new MethodNotFoundException(LocalMessages::get("error.identifier.method.notfound", $this->name));
        }
        if ($value instanceof \ReflectionMethod) {
            $method = $value;
            if ($returnType != null && $returnType != $method->getReturnType()) {
                throw new MethodNotFoundException(LocalMessages::get("error.identifier.method.notfound", $this->name));
            }
            return $method;
        }
        throw new MethodNotFoundException(LocalMessages::get("error.identifier.method.notamethod", $this->name, gettype($value)));
    }

    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo
    {
        $method = $this->getMethod($bindings, $context, $returnType, $paramTypes);
        return new MethodInfo($method->getName(), $method->getReturnType(), $paramTypes);
    }

    public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $params = [])
    {
        $method = $this->getMethod($bindings, $context, $returnType, $paramTypes);
        try {
            return $method->invoke(null, ...$params);
        } catch (\Exception $e) {
            throw new ELException(LocalMessages::get("error.identifier.method.invocation", $this->name));
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= $bindings != null && $bindings->isVariableBound($this->index) ? "<var>" : $this->name;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getName(): string
    {
        return $this->name;
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
