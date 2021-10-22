<?php

namespace BpmPlatform\Engine\Variable\Type;

use BpmPlatform\Engine\Variable\Impl\Type\{
    NullTypeImpl,
    BooleanTypeImpl,
    DoubleTypeImpl,
    IntegerTypeImpl,
    NumberTypeImpl,
    StringTypeImpl,
    DateTypeImpl,
    ObjectTypeImpl,
    FileValueTypeImpl
};
use BpmPlatform\Engine\Variable\Type\ValueTypeInterface;

class ValueTypeTrait
{
    public static function getNull(): PrimitiveValueTypeInterface
    {
        return new NullTypeImpl();
    }

    public static function getBoolean(): PrimitiveValueTypeInterface
    {
        return new BooleanTypeImpl();
    }

    public static function getDouble(): PrimitiveValueTypeInterface
    {
        return new DoubleTypeImpl();
    }

    public static function getInteger(): PrimitiveValueTypeInterface
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
