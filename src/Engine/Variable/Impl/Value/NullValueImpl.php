<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeTrait
};
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class NullValueImpl implements TypedValueInterface
{
    use ValueTypeTrait;

    private $isTransient;
    private static $INSTANCE;
    private static $INSTANCE_TRANSIENT;

    private function __construct(bool $isTransient)
    {
        $this->isTransient = $isTransient;
    }

    public static function getInstance(): NullValueImpl
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new NullValueImpl(false);
            self::$INSTANCE_TRANSIENT = new NullValueImpl(true);
        }
        return self::$INSTANCE;
    }

    public function getValue()
    {
        return null;
    }

    public function getType(): ?ValueTypeInterface
    {
        return $this->getNull();
    }

    public function __toString()
    {
        return "Untyped 'null' value";
    }

    public function isTransient(): bool
    {
        return $this->isTransient;
    }
}
