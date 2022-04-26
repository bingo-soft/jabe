<?php

namespace Tests\Bpmn\Engine\Variable;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Variable\{
    SerializationDataFormats,
    Variables
};

class VariableApiTest extends TestCase
{
    private const DESERIALIZED_OBJECT_VAR_NAME = "deserializedObject";
    private const SERIALIZATION_DATA_FORMAT_NAME = "data-format-name";

    public function testCreateObjectVariables(): void
    {
        $DESERIALIZED_OBJECT_VAR_VALUE = new \stdClass();
        $variables = Variables::createVariables()
          ->putValue(self::DESERIALIZED_OBJECT_VAR_NAME, Variables::objectValue($DESERIALIZED_OBJECT_VAR_VALUE));

        $this->assertEquals($DESERIALIZED_OBJECT_VAR_VALUE, $variables->get(self::DESERIALIZED_OBJECT_VAR_NAME));

        $untypedValue = $variables->getValueTyped(self::DESERIALIZED_OBJECT_VAR_NAME)->getValue();
        $this->assertEquals($DESERIALIZED_OBJECT_VAR_VALUE, $untypedValue);

        $typedValue = $variables->getValueTyped(self::DESERIALIZED_OBJECT_VAR_NAME)->getValue(\stdClass::class);
        $this->assertEquals($DESERIALIZED_OBJECT_VAR_VALUE, $typedValue);

        // object type name is not yet available
        $this->assertNull($variables->getValueTyped(self::DESERIALIZED_OBJECT_VAR_NAME)->getObjectTypeName());
        // class is available
        $this->assertEquals(
            get_class($DESERIALIZED_OBJECT_VAR_VALUE),
            $variables->getValueTyped(self::DESERIALIZED_OBJECT_VAR_NAME)->getObjectType()
        );

        $variables = Variables::createVariables()
            ->putValue(self::DESERIALIZED_OBJECT_VAR_NAME, Variables::objectValue($DESERIALIZED_OBJECT_VAR_VALUE)
            ->serializationDataFormat(self::SERIALIZATION_DATA_FORMAT_NAME));

        $this->assertEquals($DESERIALIZED_OBJECT_VAR_VALUE, $variables->get(self::DESERIALIZED_OBJECT_VAR_NAME));
    }

    public function testVariableMapWithoutCreateVariables(): void
    {
        $map1 = Variables::putValue("foo", true)->putValue("bar", 20);
        $map2 = Variables::putValueTyped("foo", Variables::booleanValue(true))
                ->putValue("bar", Variables::integerValue(20));

        $this->assertTrue($map1->equals($map2));
        $this->assertEquals($map1->values(), $map2->values());
    }

    public function testVariableMapCompatibility(): void
    {
        // test compatibility with Map<String, Object>
        $map1 = Variables::createVariables()
            ->putValue("foo", 10)
            ->putValue("bar", 20);

        // assert the map is assignable to Map<String,Object>
        $assignable = $map1;

        $map2 = Variables::createVariables()
            ->putValueTyped("foo", Variables::integerValue(10))
            ->putValueTyped("bar", Variables::integerValue(20));

        $map3 = [];
        $map3["foo"] = 10;
        $map3["bar"] = 20;

        // equals()
        $this->assertTrue($map1->equals($map2));
        $this->assertTrue($map2->equals($map3));
        $this->assertTrue($map1->equals(Variables::fromMap($map1)));
        $this->assertTrue($map1->equals(Variables::fromMap($map3)));

        // values()
        $values1 = $map1->values();
        $values2 = $map2->values();
        $values3 = array_values($map3);
        $this->assertEquals($values1, $values2);
        $this->assertEquals($values2, $values3);
    }

    public function testSerializationDataFormats(): void
    {
        $DESERIALIZED_OBJECT_VAR_VALUE = new \stdClass();

        $objectValue = Variables::objectValue($DESERIALIZED_OBJECT_VAR_VALUE)
                       ->serializationDataFormat(SerializationDataFormats::PHP)->create();
        $this->assertEquals(SerializationDataFormats::PHP, $objectValue->getSerializationDataFormat());

        $objectValue = Variables::objectValue($DESERIALIZED_OBJECT_VAR_VALUE)
                       ->serializationDataFormat(SerializationDataFormats::JSON)->create();
        $this->assertEquals(SerializationDataFormats::JSON, $objectValue->getSerializationDataFormat());

        $objectValue = Variables::objectValue($DESERIALIZED_OBJECT_VAR_VALUE)
                       ->serializationDataFormat(SerializationDataFormats::XML)->create();
        $this->assertEquals(SerializationDataFormats::XML, $objectValue->getSerializationDataFormat());
    }

    public function testEmptyVariableMapAsVariableContext(): void
    {
        $varContext = Variables::createVariables()->asVariableContext();
        $this->assertCount(0, $varContext->keySet());
        $this->assertNull($varContext->resolve("nonExisting"));
        $this->assertFalse($varContext->containsVariable("nonExisting"));
    }

    public function testEmptyVariableContext(): void
    {
        $varContext = Variables::emptyVariableContext();
        $this->assertCount(0, $varContext->keySet());
        $this->assertNull($varContext->resolve("nonExisting"));
        $this->assertFalse($varContext->containsVariable("nonExisting"));
    }

    public function testVariableMapAsVariableContext(): void
    {
        $varContext = Variables::createVariables()
            ->putValueTyped("someValue", Variables::integerValue(1))->asVariableContext();

        $this->assertCount(1, $varContext->keySet());

        $this->assertNull($varContext->resolve("nonExisting"));
        $this->assertFalse($varContext->containsVariable("nonExisting"));

        $this->assertEquals(1, $varContext->resolve("someValue")->getValue());
        $this->assertTrue($varContext->containsVariable("someValue"));
    }

    public function testTransientVariables(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $variableMap = Variables::createVariables()
                         ->putValueTyped("foo", Variables::doubleValue(10.0, true))
                         ->putValueTyped("bar", Variables::integerValue(10, true))
                         ->putValueTyped("aa", Variables::booleanValue(true, true))
                         ->putValueTyped("bb", Variables::stringValue("bb", true))
                         ->putValueTyped("val", Variables::dateValue(time(), true))
                         ->putValueTyped("var", Variables::objectValue(new \stdClass(), true)->create())
                         ->putValueTyped("file", Variables::fileValue($path)->setTransient(true)->create())
                         ->putValueTyped("hi", Variables::untypedValue("stringUntyped", true))
                         ->putValueTyped("null", Variables::untypedValue(null, true))
                         ->putValueTyped("ser", Variables::serializedObjectValue("{\"name\" : \"foo\"}", true)->create());

        foreach ($variableMap->keySet() as $key) {
            $value = $variableMap->getValueTyped($key);
            $this->assertTrue($value->isTransient());
        }
    }
}
