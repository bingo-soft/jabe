<?php

namespace Tests\Bpmn\Engine\Variable;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeTrait
};
use BpmPlatform\Engine\Variable\Impl\Value\NullValueImpl;

class PrimitiveValueTest extends TestCase
{
    protected const LOCAL_DATE_VALUE = "2015-09-18";
    protected const LOCAL_TIME_VALUE = "10:00:00";
    protected const PERIOD_VALUE = "P14D";
    protected $values = [];

    protected function setUp(): void
    {
        $date = time();
        $this->values = [
            [
                ValueTypeTrait::getString(), "someString", Variables::stringValue("someString"), Variables::stringValue(null)
            ],
            [
                ValueTypeTrait::getInteger(), 1, Variables::integerValue(1), Variables::integerValue(null)
            ],
            [
                ValueTypeTrait::getBoolean(), true, Variables::booleanValue(true), Variables::booleanValue(null)
            ],
            [
                ValueTypeTrait::getDouble(), 1.0, Variables::doubleValue(1.0), Variables::doubleValue(null)
            ],
            [
                ValueTypeTrait::getDate(), $date, Variables::dateValue($date), Variables::dateValue(null)
            ]
        ];
    }

    public function testCreatePrimitiveVariableUntyped(): void
    {
        foreach ($this->values as $record) {
            $valueType = $record[0];
            $value = $record[1];
            $typedValue = $record[2];
            $nullValue = $record[3];
            $variableName = "variable";
            $variables = Variables::createVariables()->putValue($variableName, $value);

            $this->assertEquals($value, $variables->get($variableName));
            $this->assertEquals($value, $variables->getValueTyped($variableName)->getValue());

            // no type information present
            $typedValue = $variables->getValueTyped($variableName);
            if (!($typedValue instanceof NullValueImpl)) {
                $this->assertNull($typedValue->getType());
                $this->assertEquals($variables->get($variableName), $typedValue->getValue());
            } else {
                $this->assertNull($typedValue->getType());
            }
        }
    }

    public function testCreatePrimitiveVariableTyped(): void
    {
        foreach ($this->values as $record) {
            $valueType = $record[0];
            $value = $record[1];
            $typedValue = $record[2];
            $nullValue = $record[3];
            $variableName = "variable";
            $variables = Variables::createVariables()->putValue($variableName, $typedValue);

            $this->assertEquals($value, $variables->get($variableName));
            $this->assertEquals($valueType, $variables->getValueTyped($variableName)->getType());

            // get wrapper
            $stringValue = $variables->getValueTyped($variableName)->getValue();
            $this->assertEquals($value, $stringValue);
        }
    }

    public function testCreatePrimitiveVariableNull(): void
    {
        foreach ($this->values as $record) {
            $valueType = $record[0];
            $value = $record[1];
            $typedValue = $record[2];
            $nullValue = $record[3];
            $variableName = "variable";
            $variables = Variables::createVariables()->putValue($variableName, $nullValue);

            $this->assertEquals(null, $variables->get($variableName));
            $this->assertEquals($valueType, $variables->getValueTyped($variableName)->getType());

            // get wrapper
            $stringValue = $variables->getValueTyped($variableName)->getValue();
            $this->assertNull($stringValue);
        }
    }
}
