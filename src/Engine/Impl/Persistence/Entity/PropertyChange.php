<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

class PropertyChange
{
    /** the empty change */
    public static $EMPTY_CHANGE;// = new PropertyChange(null, null, null);

    /** the name of the property which has been changed */
    protected $propertyName;

    /** the original value */
    protected $orgValue;

    /** the new value */
    protected $newValue;

    public static function emptyChange(): PropertyChange
    {
        if (self::$EMPTY_CHANGE === null) {
            self::$EMPTY_CHANGE = new PropertyChange(null, null, null);
        }
        return self::$EMPTY_CHANGE;
    }

    public function __construct(?string $propertyName, $orgValue, $newValue)
    {
        $this->propertyName = $propertyName;
        $this->orgValue = $orgValue;
        $this->newValue = $newValue;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    public function getOrgValue()
    {
        return $this->orgValue;
    }

    public function setOrgValue($orgValue): void
    {
        $this->orgValue = $orgValue;
    }

    public function getNewValue()
    {
        return $this->newValue;
    }

    public function setNewValue($newValue): void
    {
        $this->newValue = $newValue;
    }

    public function getNewValueString(): ?string
    {
        return $this->valueAsString($this->newValue);
    }

    public function getOrgValueString(): ?string
    {
        return $this->valueAsString($this->orgValue);
    }

    protected function valueAsString($value)
    {
        if ($value === null) {
            return null;
        } else {
            return strval($value);
        }
    }
}
