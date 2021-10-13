<?php

namespace BpmPlatform\Engine\Variable\Type;

use BpmPlatform\Engine\Variable\Impl\Type\{
    NullTypeImpl,
    BooleanTypeImpl,
    DoubleTypeImpl,
    IntegerTypeImpl,
    StringTypeImpl,
    DateTypeImpl,
    ObjectTypeImpl,
    FileTypeImpl
};

trait ValueTypeTrait
{
    public function getNull(): PrimitiveValueTypeInterface
    {
        return new NullTypeImpl();
    }

    public function getBoolean(): PrimitiveValueTypeInterface
    {
        return new BooleanTypeImpl();
    }

    public function getDouble(): PrimitiveValueTypeInterface
    {
        return new DoubleTypeImpl();
    }

    public function getInteger(): PrimitiveValueTypeInterface
    {
        return new IntegerTypeImpl();
    }

    public function getString(): PrimitiveValueTypeInterface
    {
        return new StringTypeImpl();
    }

    public function getDate(): PrimitiveValueTypeInterface
    {
        return new DateTypeImpl();
    }

    public function getObject(): PrimitiveValueTypeInterface
    {
        return new ObjectTypeImpl();
    }

    public function getFile(): PrimitiveValueTypeInterface
    {
        return new FileTypeImpl();
    }
}
