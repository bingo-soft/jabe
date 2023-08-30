<?php

namespace Jabe\Impl\Persistence\Entity;

class SuspensionState
{
    protected int $stateCode = 0;

    protected $name;

    private static $ACTIVE;

    private static $SUSPENDED;

    public function __serialize(): array
    {
        return [
            'stateCode' => $this->stateCode,
            'name' => $this->name
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->stateCode = $data['stateCode'];
        $this->name = $data['name'];
    }

    public static function active(): SuspensionState
    {
        if (self::$ACTIVE === null) {
            self::$ACTIVE = new SuspensionStateImpl(1, "active");
        }
        return self::$ACTIVE;
    }

    public static function suspended(): SuspensionState
    {
        if (self::$SUSPENDED === null) {
            self::$SUSPENDED = new SuspensionStateImpl(2, "suspended");
        }
        return self::$SUSPENDED;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStateCode(): int
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
        if ($obj === null) {
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
