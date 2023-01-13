<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\{
    ValueTypeInterface
};
use Jabe\Variable\Value\TypedValueInterface;
use Jabe\Variable\Type\ValueType;

class NullValueImpl implements TypedValueInterface
{
    private bool $isTransient = false;
    public static $INSTANCE;
    public static $INSTANCE_TRANSIENT;

    private function __construct(bool $isTransient)
    {
        $this->isTransient = $isTransient;
    }

    public static function getInstance(bool $isTransient): NullValueImpl
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new NullValueImpl(false);
            self::$INSTANCE_TRANSIENT = new NullValueImpl(true);
        }
        if ($isTransient) {
            return self::$INSTANCE_TRANSIENT;
        } else {
            return self::$INSTANCE;
        }
    }

    public function getValue()
    {
        return null;
    }

    public function getType(): ?ValueTypeInterface
    {
        return ValueType::getNull();
    }

    public function __toString()
    {
        return "Untyped 'null' value";
    }

    public function serialize()
    {
        return serialize("Untyped 'null' value");
    }

    public function unserialize($data)
    {
    }

    public function isTransient(): bool
    {
        return $this->isTransient;
    }
}
