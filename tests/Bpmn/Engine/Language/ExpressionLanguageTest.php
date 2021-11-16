<?php

namespace Tests\Bpmn\Engine\Language;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Engine\Impl\Language\{
    Builder,
    Feature,
    ExpressionFactoryImpl,
    SimpleContext,
    SimpleResolver,
    TreeStore,
    TreeMethodExpression,
    TreeValueExpression
};
use BpmPlatform\Engine\Impl\Expression\ObjectELResolver;

class SimpleContextTest extends TestCase
{
    public function testNumericVariables(): void
    {
        $factory = new ExpressionFactoryImpl();
        $context = new SimpleContext();

        $context->setVariable("e", $factory->createValueExpression(null, null, M_E, "double"));
        $context->setVariable("pi", $factory->createValueExpression(null, null, M_PI, "double"));

        $vmapper = $context->getVariableMapper();

        $this->assertEquals(M_E, $vmapper->resolveVariable("e")->getValue($context));
        $this->assertEquals(M_PI, $vmapper->resolveVariable("pi")->getValue($context));

        $expr = $factory->createValueExpression($context, '${e + 1}', null, "double");
        $this->assertEquals(M_E + 1, $expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${pi}', null, "double");
        $this->assertEquals(M_PI, $expr->getValue($context));

        $context->setVariable("a", $factory->createValueExpression(null, null, 1, "integer"));
        $context->setVariable("b", $factory->createValueExpression(null, null, 2, "integer"));
        $expr = $factory->createValueExpression($context, '${a + b}', null, "integer");
        $this->assertEquals(3, $expr->getValue($context));

        $context->setVariable("c", $factory->createValueExpression(null, null, 3, "integer"));
        $expr = $factory->createValueExpression($context, '${a + b * c}', null, "integer");
        $this->assertEquals(7, $expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${(a + b) * c}', null, "integer");
        $this->assertEquals(9, $expr->getValue($context));
    }

    public function testNumericMethods(): void
    {
        $factory = new ExpressionFactoryImpl();
        $context = new SimpleContext();

        $wrapper =  new \ReflectionClass(SimpleClass::class);

        $context->setVariable("e", $factory->createValueExpression(null, null, M_E, "double"));
        $context->setVariable("pi", $factory->createValueExpression(null, null, M_PI, "double"));
        $context->setVariable("arr", $factory->createValueExpression(null, null, [1, 2, 3], "array"));

        $context->setFunction("", "sin", $wrapper->getMethod("sin"));
        $context->setFunction("", "cos", $wrapper->getMethod("cos"));
        $context->setFunction("", "in_array", $wrapper->getMethod("inArray"));

        $fmapper = $context->getFunctionMapper();

        $this->assertEquals(1, $fmapper->resolveFunction("", "sin")->invoke(null, M_PI / 2));
        $this->assertEquals(0, $fmapper->resolveFunction("", "cos")->invoke(null, M_PI / 2));

        $expr = $factory->createValueExpression($context, '${sin(pi / 2) + cos(pi / 4) * sin(pi / 3)}', null, "double");
        $this->assertEquals(sin(M_PI / 2) + cos(M_PI / 4) * sin(M_PI / 3), $expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${in_array(2, arr)}', null, "boolean");
        $this->assertTrue($expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${in_array(5, arr)}', null, "boolean");
        $this->assertFalse($expr->getValue($context));
    }

    public function testBooleanMethods(): void
    {
        $factory = new ExpressionFactoryImpl();
        $context = new SimpleContext();

        $expr = $factory->createValueExpression($context, '${true}', null, "boolean");
        $this->assertTrue($expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${false}', null, "boolean");
        $this->assertFalse($expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${true or false}', null, "boolean");
        $this->assertTrue($expr->getValue($context));

        $expr = $factory->createValueExpression($context, '${true and false}', null, "boolean");
        $this->assertFalse($expr->getValue($context));
    }

    public function testMethodInvocation(): void
    {
        $context = new SimpleContext(new SimpleResolver(new ObjectELResolver()));
        $store = new TreeStore(new Builder([Feature::METHOD_INVOCATIONS]), null);

        $simple = new SimpleClass();
        $factory = new ExpressionFactoryImpl();

        $context->getELResolver()->setValue($context, null, "base", $simple);
        $expr = new TreeMethodExpression($store, null, null, null, '${base.foo}', null);
        $this->assertEquals(1, $expr->invoke($context));

        $ser = serialize($expr);
        $des = unserialize($ser);
        $this->assertEquals(1, $des->invoke($context));
    }

    public function testExpressionString(): void
    {
        $store = new TreeStore(new Builder(), null);
        $this->assertEquals("foo", (new TreeValueExpression($store, null, null, null, "foo", "object"))->getExpressionString());
    }

    public function testIsDeferred(): void
    {
        $store = new TreeStore(new Builder(), null);
        $this->assertFalse((new TreeValueExpression($store, null, null, null, "foo", "object"))->isDeferred());
        $this->assertFalse((new TreeValueExpression($store, null, null, null, '${foo}', "object"))->isDeferred());
        $this->assertTrue((new TreeValueExpression($store, null, null, null, "#{foo}", "object"))->isDeferred());
    }

    public function testGetExpectedType(): void
    {
        $store = new TreeStore(new Builder(), null);
        $this->assertEquals("object", (new TreeValueExpression($store, null, null, null, '${foo}', "object"))->getExpectedType());
        $this->assertEquals("string", (new TreeValueExpression($store, null, null, null, '${foo}', "string"))->getExpectedType());
    }

    public function testGetType(): void
    {
        $store = new TreeStore(new Builder(), null);
        $context = new SimpleContext();
        $this->assertFalse((new TreeValueExpression($store, null, null, null, '${property_foo}', "object"))->isReadOnly($context));
    }
}
