<?php

namespace BpmPlatform\Engine\Impl\Expression;

abstract class MethodExpression extends Expression
{
    /**
     * Evaluates the expression relative to the provided context, and returns information about the
     * actual referenced method.
     *
     * @param context
     *            The context of this evaluation.
     * @return The context of this evaluation
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws MethodNotFoundException
     *             if no suitable method can be found.
     * @throws ELException
     *             if an exception was thrown while performing property or variable resolution. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available.
     */
    abstract public function getMethodInfo(ELContext $context): MethodInfo;

    /**
     * If a String literal is specified as the expression, returns the String literal coerced to the
     * expected return type of the method signature. An ELException is thrown if expectedReturnType
     * is void or if the coercion of the String literal to the expectedReturnType yields an error
     * (see Section "1.16 Type Conversion" of the EL specification). If not a String literal,
     * evaluates the expression relative to the provided context, invokes the method that was found
     * using the supplied parameters, and returns the result of the method invocation. Any
     * parameters passed to this method is ignored if isLiteralText() is true.
     *
     * @param context
     *            The context of this evaluation.
     * @param params
     *            The parameters to pass to the method, or null if no parameters.
     * @return the result of the method invocation (null if the method has a void return type).
     * @throws NullPointerException
     *             if context is null
     * @throws PropertyNotFoundException
     *             if one of the property resolutions failed because a specified variable or
     *             property does not exist or is not readable.
     * @throws MethodNotFoundException
     *             if no suitable method can be found.
     * @throws ELException
     *             if a String literal is specified and expectedReturnType of the MethodExpression
     *             is void or if the coercion of the String literal to the expectedReturnType yields
     *             an error (see Section "1.16 Type Conversion").
     * @throws ELException
     *             if an exception was thrown while performing property or variable resolution. The
     *             thrown exception must be included as the cause property of this exception, if
     *             available. If the exception thrown is an InvocationTargetException, extract its
     *             cause and pass it to the ELException constructor.
     */
    abstract public function invoke(ELContext $context, array $params);

    /**
     * Return whether this MethodExpression was created with parameters.
     *
     * <p>
     * This method must return <code>true</code> if and only if parameters are specified in the EL,
     * using the expr-a.expr-b(...) syntax.
     * </p>
     *
     * @return <code>true</code> if the MethodExpression was created with parameters,
     *         <code>false</code> otherwise.
     * @since 2.2
     */
    public function isParmetersProvided(): bool
    {
        return false;
    }
}
