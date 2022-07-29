<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ELContext,
    MethodInfo,
    ValueReference
};

interface ExpressionNode extends Node
{
    /**
     * @return bool true if this node represents literal text
     */
    public function isLiteralText(): bool;

    /**
     * @return bool true if the subtree rooted at this node could be used as an lvalue
     *         expression (identifier or property sequence with non-literal prefix).
     */
    public function isLeftValue(): bool;

    /**
     * @return bool true if the subtree rooted at this node is a method invocation.
     */
    public function isMethodInvocation(): bool;

    /**
     * Evaluate node.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @param expectedType
     *            result type
     * @return evaluated node, coerced to the expected type
     */
    public function getValue(Bindings $bindings, ELContext $context, string $expectedType);

    /**
     * Get value reference.
     *
     * @param bindings
     * @param context
     * @return value reference
     */
    public function getValueReference(Bindings $bindings, ELContext $context): ?ValueReference;

    /**
     * Get the value type accepted in {@link #setValue(Bindings, ELContext, Object)}.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @return accepted type or <code>null</code> for non-lvalue nodes
     */
    public function getType(Bindings $bindings, ELContext $context): ?string;

    /**
     * Determine whether {@link #setValue(Bindings, ELContext, Object)} will throw a
     * {@link org.camunda.bpm.engine.impl.javax.el.PropertyNotWritableException}.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @return bool true if this a read-only expression node
     */
    public function isReadOnly(Bindings $bindings, ELContext $context): bool;

    /**
     * Assign value.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @param value
     *            value to set
     */
    public function setValue(Bindings $bindings, ELContext $context, $value): void;

    /**
     * Get method information. If this is a non-lvalue node, answer <code>null</code>.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @param returnType
     *            expected method return type (may be <code>null</code> meaning don't care)
     * @param paramTypes
     *            expected method argument types
     * @return method information or <code>null</code>
     */
    public function getMethodInfo(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = []): ?MethodInfo;

    /**
     * Invoke method.
     *
     * @param bindings
     *            bindings containing variables and functions
     * @param context
     *            evaluation context
     * @param returnType
     *            expected method return type (may be <code>null</code> meaning don't care)
     * @param paramTypes
     *            expected method argument types
     * @param paramValues
     *            parameter values
     * @return result of the method invocation
     */
    //public function invoke(Bindings $bindings, ELContext $context, ?string $returnType = null, ?array $paramTypes = [], ?array $paramValues = []);

    /**
     * Get the canonical expression string for this node. Variable and funtion names will be
     * replaced in a way such that two expression nodes that have the same node structure and
     * bindings will also answer the same value here.
     * <p/>
     * For example, <code>"${foo:bar()+2*foobar}"</code> may lead to
     * <code>"${&lt;fn>() + 2 * &lt;var>}"</code> if <code>foobar</code> is a bound variable.
     * Otherwise, the structural id would be <code>"${&lt;fn>() + 2 * foobar}"</code>.
     * <p/>
     * If the bindings is <code>null</code>, the full canonical subexpression is returned.
     */
    public function getStructuralId(Bindings $bindings): string;
}
