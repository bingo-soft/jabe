<?php

namespace BpmPlatform\Engine\Impl\Expression;

abstract class Expression implements \Serializable
{
    /**
     * Determines whether the specified object is equal to this Expression. The result is true if
     * and only if the argument is not null, is an Expression object that is the of the same type
     * (ValueExpression or MethodExpression), and has an identical parsed representation. Note that
     * two expressions can be equal if their expression Strings are different. For example,
     * ${fn1:foo()} and ${fn2:foo()} are equal if their corresponding FunctionMappers mapped fn1:foo
     * and fn2:foo to the same method.
     *
     * @param obj
     *            the Object to test for equality.
     * @return true if obj equals this Expression; false otherwise.
     */
    abstract public function equals($obj): bool;

    /**
     * Returns the original String used to create this Expression, unmodified. This is used for
     * debugging purposes but also for the purposes of comparison (e.g. to ensure the expression in
     * a configuration file has not changed). This method does not provide sufficient information to
     * re-create an expression. Two different expressions can have exactly the same expression
     * string but different function mappings. Serialization should be used to save and restore the
     * state of an Expression.
     *
     * @return The original expression String.
     */
    abstract public function getExpressionString(): string;

    /**
     * Returns whether this expression was created from only literal text. This method must return
     * true if and only if the expression string this expression was created from contained no
     * unescaped EL delimeters (${...} or #{...}).
     *
     * @return true if this expression was created from only literal text; false otherwise.
     */
    abstract public function isLiteralText(): bool;
}
