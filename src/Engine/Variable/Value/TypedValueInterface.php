<?php

namespace BpmPlatform\Engine\Variable\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeInterface;

interface TypedValueInterface extends \Serializable
{
    /**
     * The actual value. May be null in case the value is null.
     *
     * @return the value
     */
    public function getValue();

    /**
     * The type of the value. See ValueType for a list of built-in ValueTypes.
     * @return the type of the value.
     */
    public function getType(): ?ValueTypeInterface;

    /**
     * Indicator for transience of the value
     * @return isTransient
     */
    public function isTransient(): bool;
}
