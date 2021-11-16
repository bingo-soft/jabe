<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\{
    ELContext,
    ELException,
    ExpressionFactory,
    ValueExpression,
    MethodExpression
};

class ExpressionFactoryImpl extends ExpressionFactory
{
    public static $PROP_METHOD_INVOCATIONS = "methodInvocations";
    public static $PROP_VAR_ARGS = "varArgs";
    public static $PROP_NULL_PROPERTIES = "nullProperties";

    private $store;
    private $converter;

    public function __construct(?Profile $profile = null, ?TreeStore $store = null, ?TypeConverter $converter = null)
    {
        if ($profile == null) {
            $profile = new Profile(Profile::DEFAULT);
        }
        $features = $profile->features();
        if ($store == null && $converter == null) {
            $this->store = $this->createTreeStore($features);
            $this->converter = TypeConverter::getDefault();
        } elseif ($store != null && $converter == null) {
            $this->store = $store;
            $this->converter = TypeConverter::getDefault();
        } elseif ($store == null && $converter != null) {
            $this->store = $this->createTreeStore($features);
            $this->converter = $converter;
        } else {
            $this->store = $store;
            $this->converter = $converter;
        }
    }

    /**
     * Create the factory's tree store.
     */
    protected function createTreeStore(array $features): TreeStore
    {
        // create builder
        return new TreeStore(
            new Builder($features),
            new Cache()
        );
    }

    public function coerceToType($obj, string $targetType)
    {
        return $this->converter->convert($obj, $targetType);
    }

    public function createValueExpression(?ELContext $context = null, ?string $expression = null, $instance = null, ?string $expectedType = null): ValueExpression
    {
        if ($instance != null) {
            return new ObjectValueExpression($this->converter, $instance, $expectedType);
        }
        return new TreeValueExpression(
            $this->store,
            $context->getFunctionMapper(),
            $context->getVariableMapper(),
            $this->converter,
            $expression,
            $expectedType
        );
    }

    public function createMethodExpression(
        ELContext $context,
        string $expression,
        ?string $expectedReturnType = null,
        ?array $expectedParamTypes = []
    ): MethodExpression {
        return new TreeMethodExpression(
            $this->store,
            $context->getFunctionMapper(),
            $context->getVariableMapper(),
            $this->converter,
            $expression,
            $expectedReturnType,
            $expectedParamTypes
        );
    }
}
