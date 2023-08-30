<?php

namespace Jabe\Variable\Type;

use Jabe\Variable\Impl\Type\{
    NullTypeImpl,
    BooleanTypeImpl,
    BytesTypeImpl,
    DoubleTypeImpl,
    IntegerTypeImpl,
    NumberTypeImpl,
    StringTypeImpl,
    DateTypeImpl,
    ObjectTypeImpl,
    FileValueTypeImpl
};
use Jabe\Variable\Type\ValueTypeInterface;

class ValueType
{
    public static function getNull(): PrimitiveValueTypeInterface
    {
        return new NullTypeImpl();
    }

    public static function getBoolean(): PrimitiveValueTypeInterface
    {
        return new BooleanTypeImpl();
    }

    public static function getBytes(): PrimitiveValueTypeInterface
    {
        return new BytesTypeImpl();
    }

    public static function getDouble(): PrimitiveValueTypeInterface
    {
        return new DoubleTypeImpl();
    }

    public static function getInteger(): PrimitiveValueTypeInterface
    {
        return new IntegerTypeImpl();
    }

    public static function getLong(): PrimitiveValueTypeInterface
    {
        return new IntegerTypeImpl();
    }

    public static function getString(): PrimitiveValueTypeInterface
    {
        return new StringTypeImpl();
    }

    public static function getDate(): PrimitiveValueTypeInterface
    {
        return new DateTypeImpl();
    }

    public static function getObject(): ValueTypeInterface
    {
        return new ObjectTypeImpl();
    }

    public static function getFile(): FileValueTypeInterface
    {
        return new FileValueTypeImpl();
    }
}
