<?php

namespace Jabe\Engine\Impl\Util\El;

abstract class ExpressionFactory
{
    /**
     * Create an ExpressionFactory instance.
     *
     * @param properties
     *            Properties passed to the constructor of the implementation.
     * @return an instance of ExpressionFactory
     * @param className
     *            The name of the ExpressionFactory class.
     * @param classLoader
     *            The class loader to be used to load the class.
     * @return An instance of ExpressionFactory.
     * @throws ELException
     *             if the class could not be found or if it is not a subclass of ExpressionFactory
     *             or if the class could not be instantiated.
     */
    private static function newInstance(?array $properties = null, string $className): ExpressionFactory
    {
        if ($properties != null) {
            return $className::newInstance(...$properties);
        }
        return $className::newInstance();
    }

    /**
     * Coerces an object to a specific type according to the EL type conversion rules. An
     * {@link ELException} is thrown if an error results from applying the conversion rules.
     *
     * @param obj
     *            The object to coerce.
     * @param targetType
     *            The target type for the coercion.
     * @return the coerced object
     * @throws ELException
     *             if an error results from applying the conversion rules.
     */
    abstract public function coerceToType($obj, string $targetType);

    /**
     * Parses an expression into a {@link MethodExpression} for later evaluation. Use this method
     * for expressions that refer to methods. If the expression is a String literal, a
     * MethodExpression is created, which when invoked, returns the String literal, coerced to
     * expectedReturnType. An ELException is thrown if expectedReturnType is void or if the coercion
     * of the String literal to the expectedReturnType yields an error (see Section "1.16 Type
     * Conversion"). This method should perform syntactic validation of the expression. If in doing
     * so it detects errors, it should raise an ELException.
     *
     * @param context
     *            The EL context used to parse the expression. The FunctionMapper and VariableMapper
     *            stored in the ELContext are used to resolve functions and variables found in the
     *            expression. They can be null, in which case functions or variables are not
     *            supported for this expression. The object returned must invoke the same functions
     *            and access the same variable mappings regardless of whether the mappings in the
     *            provided FunctionMapper and VariableMapper instances change between calling
     *            ExpressionFactory.createMethodExpression() and any method on MethodExpression.
     *            Note that within the EL, the ${} and #{} syntaxes are treated identically. This
     *            includes the use of VariableMapper and FunctionMapper at expression creation time.
     *            Each is invoked if not null, independent of whether the #{} or ${} syntax is used
     *            for the expression.
     * @param expression
     *            The expression to parse
     * @param expectedReturnType
     *            The expected return type for the method to be found. After evaluating the
     *            expression, the MethodExpression must check that the return type of the actual
     *            method matches this type. Passing in a value of null indicates the caller does not
     *            care what the return type is, and the check is disabled.
     * @param expectedParamTypes
     *            The expected parameter types for the method to be found. Must be an array with no
     *            elements if there are no parameters expected. It is illegal to pass null.
     * @return The parsed expression
     * @throws ELException
     *             Thrown if there are syntactical errors in the provided expression.
     * @throws NullPointerException
     *             if paramTypes is null.
     */
    abstract public function createMethodExpression(
        ELContext $context,
        string $expression,
        ?string $expectedReturnType = null,
        ?array $expectedParamTypes = []
    ): MethodExpression;

    /**
     * Parses an expression into a {@link ValueExpression} for later evaluation. Use this method for
     * expressions that refer to values. This method should perform syntactic validation of the
     * expression. If in doing so it detects errors, it should raise an ELException.
     *
     * @param context
     *            The EL context used to parse the expression. The FunctionMapper and VariableMapper
     *            stored in the ELContext are used to resolve functions and variables found in the
     *            expression. They can be null, in which case functions or variables are not
     *            supported for this expression. The object returned must invoke the same functions
     *            and access the same variable mappings regardless of whether the mappings in the
     *            provided FunctionMapper and VariableMapper instances change between calling
     *            ExpressionFactory.createValueExpression() and any method on ValueExpression. Note
     *            that within the EL, the ${} and #{} syntaxes are treated identically. This
     *            includes the use of VariableMapper and FunctionMapper at expression creation time.
     *            Each is invoked if not null, independent of whether the #{} or ${} syntax is used
     *            for the expression.
     * @param expression
     *            The expression to parse
     * @param expectedType
     *            The type the result of the expression will be coerced to after evaluation.
     * @return The parsed expression
     * @throws ELException
     *             Thrown if there are syntactical errors in the provided expression.
     * @throws NullPointerException
     *             if paramTypes is null.
     */
    abstract public function createValueExpression(
        ?ELContext $context = null,
        ?string $expression = null,
        $instance = null,
        ?string $expectedType = null
    ): ValueExpression;
}
