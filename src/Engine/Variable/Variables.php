<?php

namespace Jabe\Engine\Variable;

use Jabe\Engine\Variable\Context\VariableContextInterface;
use Jabe\Engine\Variable\Type\{
    ValueTypeInterface
};
use Jabe\Engine\Variable\Value\{
    BooleanValueInterface,
    BytesValueInterface,
    DateValueInterface,
    DoubleValueInterface,
    FileValueInterface,
    IntegerValueInterface,
    NumberValueInterface,
    ObjectValueInterface,
    SerializableValueInterface,
    StringValueInterface,
    TypedValueInterface
};
use Jabe\Engine\Variable\Value\Builder\{
    FileValueBuilderInterface,
    ObjectValueBuilderInterface,
    SerializedObjectValueBuilderInterface,
    TypedValueBuilderInterface
};
use Jabe\Engine\Variable\Impl\Context\EmptyVariableContext;
use Jabe\Engine\Variable\Impl\VariableMapImpl;
use Jabe\Engine\Variable\Impl\Value\{
    AbstractTypedValue,
    BooleanValueImpl,
    BytesValueImpl,
    DateValueImpl,
    DoubleValueImpl,
    FileValueImpl,
    IntegerValueImpl,
    NullValueImpl,
    NumberValueImpl,
    ObjectValueImpl,
    StringValueImpl,
    UntypedValueImpl
};
use Jabe\Engine\Variable\Impl\Value\Builder\{
    FileValueBuilderImpl,
    ObjectVariableBuilderImpl,
    SerializedObjectValueBuilderImpl
};

class Variables
{
    public static function createVariables(): VariableMapInterface
    {
        return new VariableMapImpl();
    }

    /**
     * If the given map is not a variable map, adds all its entries as untyped
     * values to a new {@link VariableMap}. If the given map is a {@link VariableMap},
     * it is returned as is.
     */
    public static function fromMap($map): VariableMapInterface
    {
        if ($map instanceof VariableMapInterface) {
            return $map;
        } else {
            return new VariableMapImpl($map);
        }
    }

    /**
     * Shortcut for {@code Variables.createVariables().putValue(name, value)}
     */
    public static function putValue(string $name, $value): VariableMapInterface
    {
        return self::createVariables()->putValue($name, $value);
    }

    /**
     * Shortcut for {@code Variables.createVariables().putValueTyped(name, value)}
     */
    public static function putValueTyped(string $name, TypedValueInterface $value): VariableMapInterface
    {
        return self::createVariables()->putValueTyped($name, $value);
    }

    /**
     * Returns a builder to create a new {@link ObjectValue} that encapsulates
     * the given {@code value}.
     */
    public static function objectValue($value, ?bool $isTransient = null): ObjectValueBuilderInterface
    {
        if ($isTransient !== null) {
            return (new ObjectVariableBuilderImpl($value))->setTransient($isTransient);
        }
        return new ObjectVariableBuilderImpl($value);
    }

    /**
     * Returns a builder to create a new {@link ObjectValue} from a serialized
     * object representation.
     */
    public static function serializedObjectValue(
        ?string $value = null,
        ?bool $isTransient = null
    ): SerializedObjectValueBuilderInterface {
        if ($value !== null && $isTransient !== null) {
            return (new SerializedObjectValueBuilderImpl())
                    ->serializedValue($value)
                    ->setTransient($isTransient);
        } elseif ($value !== null) {
            return (new SerializedObjectValueBuilderImpl())->serializedValue($value);
        }
        return new SerializedObjectValueBuilderImpl();
    }

    /**
     * Creates a new {@link IntegerValue} that encapsulates the given <code>integer</code>
     */
    public static function integerValue(?int $integer, ?bool $isTransient = null): IntegerValueInterface
    {
        return new IntegerValueImpl($integer, $isTransient ?? false);
    }

    /**
     * Creates a new {@link StringValue} that encapsulates the given <code>stringValue</code>
     */
    public static function stringValue(?string $stringValue, ?bool $isTransient = null): StringValueInterface
    {
        return new StringValueImpl($stringValue, $isTransient ?? false);
    }

    /**
     * Creates a new {@link BooleanValue} that encapsulates the given <code>booleanValue</code>
     */
    public static function booleanValue(?bool $booleanValue, ?bool $isTransient = null): BooleanValueInterface
    {
        return new BooleanValueImpl($booleanValue, $isTransient ?? false);
    }

    /**
     * Creates a new {@link BytesValue} that encapsulates the given <code>bytes</code>
     */
    public static function byteArrayValue(string $bytes, ?bool $isTransient = null): BytesValueInterface
    {
        return new BytesValueImpl($bytes, $isTransient ?? false);
    }

    /**
     * Creates a new {@link DateValue} that encapsulates the given <code>date</code>
     */
    public static function dateValue(?string $date, ?bool $isTransient = null): DateValueInterface
    {
        return new DateValueImpl($date, $isTransient ?? false);
    }

    /**
     * Creates a new {@link DoubleValue} that encapsulates the given <code>doubleValue</code>
     */
    public static function doubleValue(?float $doubleValue, ?bool $isTransient = null): DoubleValueInterface
    {
        return new DoubleValueImpl($doubleValue, $isTransient ?? false);
    }

    /**
     * Creates an abstract Number value. Note that this value cannot be used to set variables.
     * Use the specific methods {@link Variables#integerValue(Integer)}, {@link #shortValue(Short)},
     * {@link #longValue(Long)} and {@link #doubleValue(Double)} instead.
     */
    public static function numberValue($numberValue, ?bool $isTransient): NumberValueInterface
    {
        return new NumberValueImpl($numberValue, $isTransient);
    }

    /**
     * Creates a {@link TypedValue} with value {@code null} and type {@link ValueType#NULL}
     */
    public static function untypedNullValue(?bool $isTransient = null): TypedValueInterface
    {
        return NullValueImpl::getInstance($isTransient ?? false);
    }

    /**
     * Creates an untyped value, i.e. {@link TypedValue#getType()} returns <code>null</code>
     * for the returned instance.
     */
    public static function untypedValue($value, ?bool $isTransient = null): TypedValueInterface
    {
        $isTransient = $isTransient ?? false;
        if ($value === null) {
            return self::untypedNullValue($isTransient);
        }
        if ($value instanceof TypedValueInterface) {
            $transientValue = $value;
            if ($value instanceof NullValueImpl) {
                $transientValue = self::untypedNullValue($isTransient);
            } elseif ($value instanceof FileValueInterface) {
                $transientValue->setTransient($isTransient);
            } elseif ($value instanceof AbstractTypedValue) {
                $transientValue->setTransient($isTransient);
            }
            return $transientValue;
        } elseif ($value instanceof TypedValueBuilderInterface) {
            return $value->setTransient($isTransient)->create();
        } else {
            return new UntypedValueImpl($value, $isTransient);
        }
    }

    /**
     * Returns a builder to create a new {@link FileValue} with the given
     * {@code filename}.
     */
    public static function fileValue(string $filename, ?bool $isTransient = null): FileValueBuilderInterface
    {
        if ($isTransient !== null) {
            return (new FileValueBuilderImpl($filename))->setTransient($isTransient ?? false);
        }
        return new FileValueBuilderImpl($filename);
    }

    /**
     * @return an empty {@link VariableContext} (from which no variables can be resolved).
     */
    public static function emptyVariableContext(): VariableContextInterface
    {
        return EmptyVariableContext::getInstance();
    }
}
