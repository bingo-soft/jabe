<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

class SuspensionState
{
    protected $stateCode;

    protected $name;

    private static $ACTIVE;

    private static $SUSPENDED;

    public static function active(): SuspensionState
    {
        if (self::$ACTIVE == null) {
            self::$ACTIVE = new SuspensionStateImpl(1, "active");
        }
        return self::$ACTIVE;
    }

    public static function suspended(): SuspensionState
    {
        if (self::$SUSPENDED == null) {
            self::$SUSPENDED = new SuspensionStateImpl(1, "suspended");
        }
        return self::$SUSPENDED;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    protected function getStateCode(): int
    {
        return $this->stateCode;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->stateCode != $obj->stateCode) {
            return false;
        }
        return true;
    }
}
