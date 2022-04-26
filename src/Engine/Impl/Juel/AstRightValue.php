<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELException,
    MethodInfo,
    ValueReference
};

abstract class AstRightValue extends AstNode
{
/**
     * Answer <code>false</code>
     */
    public function isLiteralText(): bool
    {
        return false;
    }

    /**
     * according to the spec, the result is undefined for rvalues, so answer <code>null</code>
     */
    public function getType(Bindings $bindings, ELContext $context): ?string
    {
        return null;
    }

    /**
     * non-lvalues are always readonly, so answer <code>true</code>
     */
    public function isReadOnly(Bindings $bindings, ELContext $context): bool
    {
        return true;
    }

    /**
     * non-lvalues are always readonly, so throw an exception
     */
    public function setValue(Bindings $bindings, ELContext $context, $value): void
    {
        throw new ELException(LocalMessages::get("error.value.set.rvalue", $this->getStructuralId($bindings)));
    }

    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo
    {
        return null;
    }

    /*public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $paramValues = [])
    {
        throw new ELException(LocalMessages::get("error.method.invalid", getStructuralId($bindings)));
    }*/

    public function isLeftValue(): bool
    {
        return false;
    }

    public function isMethodInvocation(): bool
    {
        return false;
    }

    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference
    {
        return null;
    }
}
